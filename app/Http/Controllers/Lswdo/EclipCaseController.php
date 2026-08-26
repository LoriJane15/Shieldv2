<?php

namespace App\Http\Controllers\Lswdo;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lswdo\DecideEligibilityRequest;
use App\Http\Requests\Lswdo\IndexEligibilityCasesRequest;
use App\Models\EclipCase;
use App\Models\User;
use App\Services\EclipCaseWorkflowService;
use App\Services\EclipWorkflowPresentationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EclipCaseController extends Controller
{
    public function index(IndexEligibilityCasesRequest $request): View
    {
        $queueStatuses = collect(EclipCaseStatus::cases())
            ->reject(fn (EclipCaseStatus $status) => $status === EclipCaseStatus::Draft)
            ->values();

        $baseQuery = EclipCase::query()
            ->whereHas('participantAssignments', fn ($query) => $query
                ->where('user_id', $request->user()->id)
                ->where('is_active', true))
            ->whereIn('status', $queueStatuses->map(fn (EclipCaseStatus $status) => $status->value));

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'awaiting' => (clone $baseQuery)->whereIn('status', [
                EclipCaseStatus::SubmittedForEligibility->value,
                EclipCaseStatus::EligibilityReviewInProgress->value,
            ])->count(),
            'eligible' => (clone $baseQuery)->where('status', EclipCaseStatus::Eligible->value)->count(),
            'certified' => (clone $baseQuery)->where('status', EclipCaseStatus::DocumentsCertified->value)->count(),
        ];

        $filters = $request->validated();
        $casesQuery = (clone $baseQuery)->with('formerRebel');

        if ($search = $filters['search'] ?? null) {
            $casesQuery->where(function ($query) use ($search) {
                $query->where('case_number', 'like', "%{$search}%")
                    ->orWhereHas('formerRebel', function ($formerRebelQuery) use ($search) {
                        $formerRebelQuery->where('classified_id', 'like', "%{$search}%")
                            ->orWhere('firstname', 'like', "%{$search}%")
                            ->orWhere('middlename', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $filters['status'] ?? null) {
            $casesQuery->where('status', $status);
        }

        match ($filters['date'] ?? null) {
            'today' => $casesQuery->whereDate('submitted_at', today()),
            'week' => $casesQuery->whereBetween('submitted_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $casesQuery->whereBetween('submitted_at', [now()->startOfMonth(), now()->endOfMonth()]),
            default => null,
        };

        $sortDirection = ($filters['sort'] ?? 'newest') === 'oldest' ? 'asc' : 'desc';
        $cases = $casesQuery->orderBy('submitted_at', $sortDirection)
            ->orderBy('id', $sortDirection)
            ->paginate(15)
            ->withQueryString();

        return view('lswdo.eclip.index', [
            'cases' => $cases,
            'summary' => $summary,
            'statusOptions' => collect($queueStatuses)->mapWithKeys(fn (EclipCaseStatus $status) => [$status->value => $status->label()]),
            'hasActiveFilters' => filled($filters['search'] ?? null) || filled($filters['status'] ?? null) || filled($filters['date'] ?? null),
        ]);
    }

    public function show(Request $request, EclipCase $eclipCase, EclipWorkflowPresentationService $workflowPresentation): View
    {
        $this->authorize('view', $eclipCase);
        $eclipCase->load([
            'formerRebel.municipality', 'eligibilityReviews.reviewer', 'statusHistories.user',
            'workflowActivities.histories.user', 'workflowActivities.documents.uploader',
            'authenticationRequest.assignee',
            'feaDocuments.uploader',
            'assistanceReleases.releaser',
            'assistanceReleases.receivedConfirmer',
            'interventions.histories.user',
            'reintegrationPlanItems.interventions',
            'reintegrationPlanItems.histories',
            'livelihoodBeneficiaryAssistances.histories',
            'participantAssignments.user',
        ]);

        return view('lswdo.eclip.show', [
            'case' => $eclipCase,
            'workflow' => $workflowPresentation->forCase($eclipCase, $request->user()),
            'japicUsers' => User::query()->where('role', 'japic')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'feaProcessors' => User::query()->whereIn('role', ['pnp', 'afp'])->where('is_active', true)->orderBy('name')->get(['id', 'name', 'role']),
        ]);
    }

    public function decide(
        DecideEligibilityRequest $request,
        EclipCase $eclipCase,
        EclipCaseWorkflowService $workflow,
    ): RedirectResponse {
        $workflow->decideEligibility(
            $eclipCase,
            $request->user(),
            $request->validated('decision'),
            $request->validated('remarks'),
            $request->ip(),
            $request->validated('referral_status'),
            $request->validated('referred_program'),
        );

        return redirect()->route('lswdo.eclip.show', $eclipCase)->with('success', 'Eligibility decision recorded.');
    }
}
