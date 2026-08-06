<?php

namespace App\Http\Controllers\EclipAssessor;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\EclipAssessor\StoreAssessmentRevisionRequest;
use App\Models\EclipAssistanceCategory;
use App\Models\EclipCase;
use App\Services\EclipAssistanceAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $cases = EclipCase::query()
            ->where('municipality_id', $request->user()->municipality_id)
            ->whereIn('status', [
                EclipCaseStatus::DocumentsCertified->value,
                EclipCaseStatus::AssistanceAssessment->value,
                EclipCaseStatus::SubmittedForDilgReview->value,
                EclipCaseStatus::ReturnedForAssessmentRevision->value,
                EclipCaseStatus::Approved->value,
                EclipCaseStatus::Rejected->value,
            ])
            ->with(['formerRebel', 'assistanceRequest.latestRevision.category'])
            ->latest('updated_at')->paginate(15);

        return view('eclip_assessor.cases.index', ['cases' => $cases]);
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
