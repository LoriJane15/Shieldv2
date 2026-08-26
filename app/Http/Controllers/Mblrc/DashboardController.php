<?php

namespace App\Http\Controllers\Mblrc;

use App\Http\Controllers\Controller;
use App\Models\EclipCase;
use App\Models\EclipDocumentRequirement;
use App\Models\FormerRebel;
use App\Models\FrProgramStatus;
use App\Models\MblrcEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $stats = [
            'registered' => FormerRebel::count(),
            'active' => FormerRebel::where('status', 'Active')->count(),
            'reintegrated' => FormerRebel::where('status', 'Reintegrated')->count(),
            'completed' => FrProgramStatus::where('reintegration_status', 'Completed')->count(),
            'ongoing' => FrProgramStatus::where('reintegration_status', 'On-going')->count(),
            'not_started' => FrProgramStatus::where('reintegration_status', 'Not-Started')->count(),
        ];

        $programTotal = $stats['completed'] + $stats['ongoing'] + $stats['not_started'];
        $assignedEnrollmentCount = MblrcEnrollment::query()->where('assigned_user_id', $user->id)->count();
        $atRiskCount = MblrcEnrollment::query()
            ->where('assigned_user_id', $user->id)
            ->needsAttention()
            ->count();

        $now = Carbon::now(config('app.display_timezone'));
        $registeredThisMonth = FormerRebel::query()
            ->whereBetween('registered_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();
        $registeredLastMonth = FormerRebel::query()
            ->whereBetween('registered_at', [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
            ])->count();
        $registrationChange = $registeredLastMonth > 0
            ? (int) round((($registeredThisMonth - $registeredLastMonth) / $registeredLastMonth) * 100)
            : null;

        $kpis = [
            [
                'label' => 'Registered FRs',
                'value' => $stats['registered'],
                'detail' => $registrationChange === null
                    ? "{$registeredThisMonth} added this month"
                    : sprintf('%s%d%% vs last month', $registrationChange >= 0 ? '+' : '', $registrationChange),
                'progress' => $this->percentage($registeredThisMonth, max($stats['registered'], 1)),
                'icon' => 'mdi-account-multiple-outline',
                'tone' => 'info',
            ],
            [
                'label' => 'Active',
                'value' => $stats['active'],
                'detail' => $this->percentage($stats['active'], $stats['registered']).'% of registered profiles',
                'progress' => $this->percentage($stats['active'], $stats['registered']),
                'icon' => 'mdi-account-check-outline',
                'tone' => 'primary',
            ],
            [
                'label' => 'Reintegrated',
                'value' => $stats['reintegrated'],
                'detail' => $this->percentage($stats['reintegrated'], $stats['registered']).'% completion rate',
                'progress' => $this->percentage($stats['reintegrated'], $stats['registered']),
                'icon' => 'mdi-check-decagram',
                'tone' => 'success',
            ],
            [
                'label' => 'Ongoing',
                'value' => $stats['ongoing'],
                'detail' => $this->percentage($stats['ongoing'], $programTotal).'% of program records',
                'progress' => $this->percentage($stats['ongoing'], $programTotal),
                'icon' => 'mdi-progress-clock',
                'tone' => 'warning',
            ],
            [
                'label' => 'At Risk',
                'value' => $atRiskCount,
                'detail' => $atRiskCount === 1 ? '1 assigned enrollment overdue' : "{$atRiskCount} assigned enrollments overdue",
                'progress' => $this->percentage($atRiskCount, $assignedEnrollmentCount),
                'icon' => 'mdi-alert-circle-outline',
                'tone' => 'danger',
            ],
        ];

        $requiredDocumentIds = EclipDocumentRequirement::query()
            ->where('is_active', true)
            ->where('is_required', true)
            ->pluck('id');
        $missingDocumentCases = 0;

        if ($requiredDocumentIds->isNotEmpty()) {
            $missingDocumentCases = $this->authorizedCases($user)
                ->withCount(['documents as required_documents_count' => fn (Builder $documents) => $documents
                    ->whereIn('requirement_id', $requiredDocumentIds)])
                ->get()
                ->filter(fn (EclipCase $case) => $case->required_documents_count < $requiredDocumentIds->count())
                ->count();
        }

        $missingLocations = FormerRebel::query()
            ->where(fn (Builder $query) => $query->whereNull('latitude')->orWhereNull('longitude'))
            ->count();
        $unreadNotifications = $user->unreadNotifications()->count();

        $attentionItems = [
            [
                'count' => $atRiskCount,
                'title' => 'Integration monitoring overdue',
                'detail' => 'Assigned enrollments require completion review.',
                'tone' => 'danger',
                'icon' => 'mdi-clock-alert-outline',
                'url' => route('mblrc.enrollments.index', ['status' => 'attention']),
            ],
            [
                'count' => $missingDocumentCases,
                'title' => 'Cases missing required documents',
                'detail' => 'Review authorized E-CLIP document checklists.',
                'tone' => 'warning',
                'icon' => 'mdi-file-alert-outline',
                'url' => route('mblrc.eclip.index'),
            ],
            [
                'count' => $missingLocations,
                'title' => 'Profiles without geotags',
                'detail' => 'Location information is incomplete.',
                'tone' => 'caution',
                'icon' => 'mdi-map-marker-alert-outline',
                'url' => route('mblrc.fr.index'),
            ],
            [
                'count' => $unreadNotifications,
                'title' => 'Unread workflow notifications',
                'detail' => 'Open assigned updates and follow-up notices.',
                'tone' => 'info',
                'icon' => 'mdi-bell-ring',
                'url' => route('notifications.index'),
            ],
        ];

        return view('mblrc.dashboard', [
            'stats' => $stats,
            'kpis' => $kpis,
            'attentionItems' => $attentionItems,
            'recentActivity' => $this->recentActivity($user),
        ]);
    }

    /** Monthly program updates and cumulative registry metrics for the last seven months. */
    public function analytics(): JsonResponse
    {
        $months = collect(range(6, 0))->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths($i));
        $labels = $months->map(fn ($m) => $m->format('M Y'));

        $program = ['not_started' => [], 'ongoing' => [], 'completed' => []];
        $overall = ['registered' => [], 'active' => [], 'reintegrated' => [], 'completion_rate' => []];

        foreach ($months as $m) {
            $end = (clone $m)->endOfMonth();

            $start = (clone $m)->startOfMonth();

            $program['not_started'][] = FrProgramStatus::where('reintegration_status', 'Not-Started')
                ->whereBetween('updated_at', [$start, $end])->count();
            $program['ongoing'][] = FrProgramStatus::where('reintegration_status', 'On-going')
                ->whereBetween('updated_at', [$start, $end])->count();
            $program['completed'][] = FrProgramStatus::where('reintegration_status', 'Completed')
                ->whereBetween('updated_at', [$start, $end])->count();

            $registered = FormerRebel::where('registered_at', '<=', $end)->count();
            $active = FormerRebel::where('status', 'Active')->where('registered_at', '<=', $end)->count();
            $reintegrated = FormerRebel::where('status', 'Reintegrated')->where('registered_at', '<=', $end)->count();

            $overall['registered'][] = $registered;
            $overall['active'][] = $active;
            $overall['reintegrated'][] = $reintegrated;
            $overall['completion_rate'][] = $this->percentage($reintegrated, $registered);
        }

        return response()->json([
            'labels' => $labels,
            'program' => $program,
            'overall' => $overall,
        ]);
    }

    /** Distribution stats for pie/bar widgets. */
    public function statistics(): JsonResponse
    {
        return response()->json([
            'status' => FormerRebel::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')->pluck('count', 'status'),
            'gender' => FormerRebel::select('gender', DB::raw('COUNT(*) as count'))
                ->whereNotNull('gender')->groupBy('gender')->pluck('count', 'gender'),
            'batch' => FormerRebel::select('batch_year', DB::raw('COUNT(*) as count'))
                ->whereNotNull('batch_year')->groupBy('batch_year')->orderBy('batch_year')->pluck('count', 'batch_year'),
        ]);
    }

    private function authorizedCases(User $user): Builder
    {
        return EclipCase::query()->where(function (Builder $query) use ($user) {
            $query->where('created_by', $user->id)
                ->orWhereHas('participantAssignments', fn (Builder $participants) => $participants
                    ->where('user_id', $user->id)
                    ->where('is_active', true));
        });
    }

    private function recentActivity(User $user): Collection
    {
        $profiles = FormerRebel::query()->latest('updated_at')->limit(5)
            ->get(['id', 'classified_id', 'created_at', 'updated_at'])
            ->map(fn (FormerRebel $profile) => [
                'title' => $profile->created_at?->equalTo($profile->updated_at)
                    ? "Profile {$profile->classified_id} registered"
                    : "Profile {$profile->classified_id} updated",
                'detail' => 'FR/FVE Registry',
                'icon' => 'mdi-account-edit',
                'tone' => 'primary',
                'occurred_at' => $profile->updated_at,
                'url' => route('mblrc.fr.show', $profile),
            ]);

        $enrollments = MblrcEnrollment::query()
            ->where('assigned_user_id', $user->id)
            ->with('formerRebel:id,classified_id')
            ->latest('updated_at')->limit(5)->get()
            ->map(fn (MblrcEnrollment $enrollment) => [
                'title' => $enrollment->status === 'completed'
                    ? "Integration monitoring completed for {$enrollment->formerRebel->classified_id}"
                    : "Integration monitoring updated for {$enrollment->formerRebel->classified_id}",
                'detail' => 'Integration Monitoring',
                'icon' => $enrollment->status === 'completed' ? 'mdi-clipboard-check-outline' : 'mdi-clipboard-text-outline',
                'tone' => $enrollment->status === 'completed' ? 'success' : 'warning',
                'occurred_at' => $enrollment->updated_at,
                'url' => route('mblrc.enrollments.index').'#enrollment-'.$enrollment->id,
            ]);

        $cases = $this->authorizedCases($user)->latest('updated_at')->limit(5)->get()
            ->map(fn (EclipCase $case) => [
                'title' => "E-CLIP {$case->case_number} updated",
                'detail' => $case->status->label(),
                'icon' => 'mdi-shield-check-outline',
                'tone' => 'info',
                'occurred_at' => $case->updated_at,
                'url' => route('mblrc.eclip.show', $case),
            ]);

        return $profiles->concat($enrollments)->concat($cases)
            ->sortByDesc('occurred_at')
            ->take(6)
            ->values();
    }

    private function percentage(int $part, int $whole): int
    {
        return $whole > 0 ? (int) round(($part / $whole) * 100) : 0;
    }
}
