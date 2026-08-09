<?php

namespace App\Http\Controllers\EclipAssessor;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\EclipAssessor\IndexAssessmentCasesRequest;
use App\Http\Requests\EclipAssessor\StoreAssessmentRevisionRequest;
use App\Models\EclipAssistanceCategory;
use App\Models\EclipCase;
use App\Services\EclipAssistanceAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(IndexAssessmentCasesRequest $request): View
    {
        $queueStatuses = [
            EclipCaseStatus::DocumentsCertified,
            EclipCaseStatus::AssistanceAssessment,
            EclipCaseStatus::SubmittedForDilgReview,
            EclipCaseStatus::ReturnedForAssessmentRevision,
            EclipCaseStatus::Approved,
            EclipCaseStatus::Rejected,
        ];

        $baseQuery = EclipCase::query()
            ->whereHas('participantAssignments', fn ($query) => $query
                ->where('user_id', $request->user()->id)
                ->where('is_active', true))
            ->whereIn('status', array_map(fn (EclipCaseStatus $status) => $status->value, $queueStatuses));

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'needs_assessment' => (clone $baseQuery)->whereIn('status', [
                EclipCaseStatus::DocumentsCertified->value,
                EclipCaseStatus::ReturnedForAssessmentRevision->value,
            ])->count(),
            'in_progress' => (clone $baseQuery)->where('status', EclipCaseStatus::AssistanceAssessment->value)->count(),
            'under_review' => (clone $baseQuery)->where('status', EclipCaseStatus::SubmittedForDilgReview->value)->count(),
            'approved' => (clone $baseQuery)->where('status', EclipCaseStatus::Approved->value)->count(),
        ];

        $filters = $request->validated();
        $casesQuery = (clone $baseQuery)->with(['formerRebel', 'assistanceRequest.latestRevision.category']);

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

        $sortDirection = ($filters['sort'] ?? 'newest') === 'oldest' ? 'asc' : 'desc';
        $cases = $casesQuery->orderBy('updated_at', $sortDirection)
            ->orderBy('id', $sortDirection)
            ->paginate(15)
            ->withQueryString();

        return view('eclip_assessor.cases.index', [
            'cases' => $cases,
            'summary' => $summary,
            'statusOptions' => collect($queueStatuses)->mapWithKeys(fn (EclipCaseStatus $status) => [$status->value => $status->label()]),
            'hasActiveFilters' => filled($filters['search'] ?? null) || filled($filters['status'] ?? null),
        ]);
    }

    public function show(EclipCase $eclipCase): View
    {
        $this->authorize('view', $eclipCase);
        $eclipCase->load(['formerRebel.municipality', 'assistanceRequest.revisions.category', 'assistanceRequest.revisions.creator', 'dilgReviews.reviewer', 'dilgReviews.revision', 'statusHistories.user']);

        return view('eclip_assessor.cases.show', [
            'case' => $eclipCase,
            'categories' => EclipAssistanceCategory::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(
        StoreAssessmentRevisionRequest $request,
        EclipCase $eclipCase,
        EclipAssistanceAssessmentService $assessments,
    ): RedirectResponse {
        $assessments->saveRevision($eclipCase, $request->validated(), $request->user(), $request->ip());

        return back()->with('success', 'Assessment revision saved.');
    }

    public function submit(Request $request, EclipCase $eclipCase, EclipAssistanceAssessmentService $assessments): RedirectResponse
    {
        $this->authorize('assessAssistance', $eclipCase);
        $assessments->submit($eclipCase, $request->user(), $request->ip());

        return back()->with('success', 'Assessment submitted for DILG review.');
    }
}
