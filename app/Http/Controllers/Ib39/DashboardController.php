<?php

namespace App\Http\Controllers\Ib39;

use App\Enums\Ib39CdrStatus;
use App\Http\Controllers\Controller;
use App\Models\Ib39CdrProcessing;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\MapBarangay;
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

        $statusCounts = MapBarangay::select('status', DB::raw('COUNT(*) as count'))
            ->whereNotNull('status')->where('status', '!=', '')
            ->groupBy('status')->pluck('count', 'status');

        $perMunicipality = MapBarangay::select('municipality', DB::raw('SUM(frs) as frs'))
            ->groupBy('municipality')
            ->having('frs', '>', 0)
            ->orderByDesc('frs')->get();

        $surfacedFrTotal = Ib39SurfacedFormerRebel::count();
        $mappedCount = MapBarangay::count();
        $totalFrCount = (int) MapBarangay::sum('frs');
        $activeAreasCount = MapBarangay::where('frs', '>', 0)->count();

        $stats = [
            'mapped' => $mappedCount,
            'total_frs' => $totalFrCount,
            'active_areas' => $activeAreasCount,
            'surfaced_total' => $surfacedFrTotal,
        ];

        $now = Carbon::now(config('app.display_timezone'));
        $surfacedThisMonth = Ib39SurfacedFormerRebel::query()
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->count();
        $surfacedLastMonth = Ib39SurfacedFormerRebel::query()
            ->whereBetween('created_at', [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
            ])->count();
        $surfacedChange = $surfacedLastMonth > 0
            ? (int) round((($surfacedThisMonth - $surfacedLastMonth) / $surfacedLastMonth) * 100)
            : null;

        $konsolidadoCount = (int) ($statusCounts['Konsolidado'] ?? 0);
        $pendingCdrCount = Ib39CdrProcessing::where('status', Ib39CdrStatus::Pending)->count();
        $firearmsCount = Ib39SurfacedFormerRebel::where('possessed_firearms', true)->count();
        $unreadNotifications = $user ? $user->unreadNotifications()->count() : 0;

        $kpis = [
            [
                'label' => 'Mapped Barangays',
                'value' => $mappedCount,
                'detail' => $mappedCount > 0 ? 'Davao del Sur coverage' : 'No areas mapped',
                'progress' => $this->percentage($activeAreasCount, max($mappedCount, 1)),
                'icon' => 'mdi-map-marker-multiple-outline',
                'tone' => 'info',
            ],
            [
                'label' => 'Areas with FR Records',
                'value' => $activeAreasCount,
                'detail' => $this->percentage($activeAreasCount, max($mappedCount, 1)).'% of mapped areas',
                'progress' => $this->percentage($activeAreasCount, max($mappedCount, 1)),
                'icon' => 'mdi-crosshairs-gps',
                'tone' => 'primary',
            ],
            [
                'label' => 'Total Former Rebels',
                'value' => $totalFrCount,
                'detail' => $surfacedChange === null
                    ? "{$surfacedThisMonth} surfaced this month"
                    : sprintf('%s%d%% vs last month', $surfacedChange >= 0 ? '+' : '', $surfacedChange),
                'progress' => $this->percentage($surfacedThisMonth, max($totalFrCount, 1)),
                'icon' => 'mdi-account-group-outline',
                'tone' => 'success',
            ],
            [
                'label' => 'Surfaced Profiles',
                'value' => $surfacedFrTotal,
                'detail' => $surfacedFrTotal === 1 ? '1 standalone record' : "{$surfacedFrTotal} standalone records",
                'progress' => $this->percentage($surfacedFrTotal, max($totalFrCount, 1)),
                'icon' => 'mdi-account-check-outline',
                'tone' => 'warning',
            ],
            [
                'label' => 'High-Threat (Konsolidado)',
                'value' => $konsolidadoCount,
                'detail' => $konsolidadoCount === 1 ? '1 high threat area' : "{$konsolidadoCount} high threat areas",
                'progress' => $this->percentage($konsolidadoCount, max($mappedCount, 1)),
                'icon' => 'mdi-alert-circle-outline',
                'tone' => 'danger',
            ],
        ];

        $attentionItems = [
            [
                'count' => $pendingCdrCount,
                'title' => 'Pending CDR debriefings',
                'detail' => 'Surfaced FR records requiring debriefing report.',
                'tone' => 'danger',
                'icon' => 'mdi-file-document-edit-outline',
                'url' => route('ib39.fr-profiles.index'),
            ],
            [
                'count' => $konsolidadoCount,
                'title' => 'Konsolidado threat areas',
                'detail' => 'Barangays with high insurgent influence recorded.',
                'tone' => 'warning',
                'icon' => 'mdi-shield-alert-outline',
                'url' => route('ib39.areas.index', ['status' => 'Konsolidado']),
            ],
            [
                'count' => $firearmsCount,
                'title' => 'Firearms surrender records',
                'detail' => 'Surfaced FR profiles with firearms indicated.',
                'tone' => 'caution',
                'icon' => 'mdi-pistol',
                'url' => route('ib39.fr-profiles.index', ['possessed_firearms' => '1']),
            ],
            [
                'count' => $unreadNotifications,
                'title' => 'Unread notifications',
                'detail' => 'Review recent administrative and system notices.',
                'tone' => 'info',
                'icon' => 'mdi-bell-ring',
                'url' => route('notifications.index'),
            ],
        ];

        $recentActivity = $this->recentActivity();

        return view('ib39.dashboard', compact('statusCounts', 'perMunicipality', 'stats', 'kpis', 'attentionItems', 'recentActivity'));
    }

    private function recentActivity(): Collection
    {
        $surfaced = Ib39SurfacedFormerRebel::query()->latest('updated_at')->limit(5)->get()
            ->map(fn (Ib39SurfacedFormerRebel $fr) => [
                'title' => $fr->created_at?->equalTo($fr->updated_at)
                    ? "Surfaced FR {$fr->reference_number} recorded"
                    : "Surfaced FR {$fr->reference_number} updated",
                'detail' => 'Surfaced FR Profiles',
                'icon' => 'mdi-account-edit',
                'tone' => 'primary',
                'occurred_at' => $fr->updated_at,
                'url' => route('ib39.fr-profiles.show', $fr),
            ]);

        $cdrs = Ib39CdrProcessing::query()->with('surfacedFormerRebel')->latest('updated_at')->limit(5)->get()
            ->map(fn (Ib39CdrProcessing $cdr) => [
                'title' => "CDR {$cdr->surfacedFormerRebel?->reference_number} status: {$cdr->status->value}",
                'detail' => 'Custodial Debriefing',
                'icon' => $cdr->status === Ib39CdrStatus::Completed ? 'mdi-file-check-outline' : 'mdi-file-clock-outline',
                'tone' => $cdr->status === Ib39CdrStatus::Completed ? 'success' : 'warning',
                'occurred_at' => $cdr->updated_at,
                'url' => route('ib39.cdr.show', $cdr),
            ]);

        $areas = MapBarangay::query()->latest('updated_at')->limit(5)->get()
            ->map(fn (MapBarangay $area) => [
                'title' => "{$area->barangay} ({$area->municipality}) updated",
                'detail' => "Status: {$area->status} · {$area->frs} FRs",
                'icon' => 'mdi-map-marker-outline',
                'tone' => 'info',
                'occurred_at' => $area->updated_at,
                'url' => route('ib39.areas.index', ['search' => $area->barangay]),
            ]);

        return $surfaced->concat($cdrs)->concat($areas)
            ->sortByDesc('occurred_at')
            ->take(6)
            ->values();
    }

    private function percentage(int $part, int $whole): int
    {
        return $whole > 0 ? (int) round(($part / $whole) * 100) : 0;
    }
}
