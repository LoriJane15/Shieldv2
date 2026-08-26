<?php

namespace App\Http\Controllers\Mblrc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mblrc\CompleteEnrollmentRequest;
use App\Http\Requests\Mblrc\IndexEnrollmentsRequest;
use App\Http\Requests\Mblrc\SearchEnrollmentBeneficiariesRequest;
use App\Http\Requests\Mblrc\StoreEnrollmentRequest;
use App\Models\FormerRebel;
use App\Models\MblrcEnrollment;
use App\Models\Municipality;
use App\Services\MblrcEnrollmentService;
use App\Services\MblrcReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(IndexEnrollmentsRequest $request): View
    {
        $filters = $request->validated();
        $assigned = MblrcEnrollment::query()->where('assigned_user_id', $request->user()->id);
        $statusCounts = (clone $assigned)
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $enrollments = (clone $assigned)
            ->with([
                'formerRebel:id,classified_id,municipality_id',
                'formerRebel.municipality:id,name',
                'verifiedMunicipality:id,name',
                'referral:id,referral_number,mblrc_enrollment_id,status,assigned_to',
                'referral.assignee:id,name',
                'referral.eclipCase:id,case_number,lswdo_referral_id,created_by,status',
            ])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $escaped = addcslashes($search, '%_\\');
                $pattern = str_starts_with(strtoupper($search), 'FR-') ? "{$escaped}%" : "%{$escaped}%";
                $query->whereHas('formerRebel', fn ($beneficiary) => $beneficiary->where('classified_id', 'like', $pattern));
            })
            ->when(($filters['status'] ?? null) === 'attention', fn ($query) => $query->needsAttention())
            ->when(in_array($filters['status'] ?? null, MblrcEnrollment::STATUSES, true), fn ($query) => $query->where('status', $filters['status']))
            ->when(($filters['sort'] ?? 'recently_updated') === 'recently_updated', fn ($query) => $query->latest('updated_at')->latest('id'))
            ->when(($filters['sort'] ?? null) === 'newest_started', fn ($query) => $query->orderByDesc('integration_started_at')->latest('id'))
            ->when(($filters['sort'] ?? null) === 'oldest_started', fn ($query) => $query->orderBy('integration_started_at')->orderBy('id'))
            ->paginate(20)
            ->withQueryString();

        return view('mblrc.enrollments.index', [
            'enrollments' => $enrollments,
            'municipalities' => Municipality::query()->orderBy('name')->get(),
            'filters' => $filters,
            'summary' => [
                'assigned' => (int) $statusCounts->sum(),
                'active' => (int) ($statusCounts['in_progress'] ?? 0),
                'completed' => (int) ($statusCounts['completed'] ?? 0),
                'attention' => (clone $assigned)->needsAttention()->count(),
            ],
            'hasActiveFilters' => filled($filters['search'] ?? null)
                || filled($filters['status'] ?? null)
                || ($filters['sort'] ?? 'recently_updated') !== 'recently_updated',
        ]);
    }

    public function beneficiaries(SearchEnrollmentBeneficiariesRequest $request): JsonResponse
    {
        $data = $request->validated();
        $search = addcslashes((string) ($data['search'] ?? ''), '%_\\');
        $beneficiaries = FormerRebel::query()
            ->with([
                'municipality:id,name',
                'mblrcEnrollment:id,former_rebel_id,assigned_user_id,status,integration_started_at',
            ])
            ->when($data['beneficiary_id'] ?? null, fn ($query, $id) => $query->whereKey($id))
            ->when(! ($data['beneficiary_id'] ?? null), fn ($query) => $query->where('classified_id', 'like', str_starts_with(strtoupper($search), 'FR-') ? "{$search}%" : "%{$search}%"))
            ->orderBy('classified_id')
            ->limit(15)
            ->get(['id', 'classified_id', 'municipality_id']);

        return response()->json($beneficiaries->map(function (FormerRebel $beneficiary) use ($request) {
            $enrollment = $beneficiary->mblrcEnrollment;
            $isAssignedEnrollment = $enrollment?->assigned_user_id === $request->user()->id;

            return [
                'id' => $beneficiary->id,
                'classified_id' => $beneficiary->classified_id,
                'municipality' => $beneficiary->municipality?->name,
                'eligible' => $enrollment === null,
                'has_existing_enrollment' => $enrollment !== null,
                'eligibility_message' => $enrollment
                    ? ($enrollment->status === 'in_progress'
                        ? 'Another active enrollment cannot be created.'
                        : 'This beneficiary has already completed integration monitoring.')
                    : 'Eligible to begin integration monitoring.',
                'existing_enrollment' => $isAssignedEnrollment ? [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status,
                    'started_at' => $enrollment->integration_started_at?->toDateString(),
                    'expected_completion_at' => $enrollment->expectedCompletionDate()?->toDateString(),
                ] : null,
            ];
        }));
    }

    public function store(StoreEnrollmentRequest $request, MblrcEnrollmentService $enrollments): RedirectResponse
    {
        $data = $request->validated();
        $enrollment = $enrollments->start(
            (int) $data['former_rebel_id'],
            $data['integration_started_at'],
            $request->user(),
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()->to(route('mblrc.enrollments.index')."#enrollment-{$enrollment->id}")
            ->with('success', 'Integration enrollment started successfully.');
    }

    public function complete(
        CompleteEnrollmentRequest $request,
        MblrcEnrollment $enrollment,
        MblrcReferralService $referrals,
    ): RedirectResponse {
        $referral = $referrals->completeIntegration(
            $enrollment,
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent(),
        );

        return back()->with('success', $referral->assigned_to
            ? 'Integration completed and assigned LSWDO referral created.'
            : 'Integration completed. The referral is pending LSWDO assignment.');
    }
}
