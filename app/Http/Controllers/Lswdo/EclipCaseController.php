<?php

namespace App\Http\Controllers\Lswdo;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Lswdo\DecideEligibilityRequest;
use App\Models\EclipCase;
use App\Models\EclipDocumentRequirement;
use App\Services\EclipCaseWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EclipCaseController extends Controller
{
    public function index(Request $request): View
    {
        $cases = EclipCase::query()
            ->where('municipality_id', $request->user()->municipality_id)
            ->whereIn('status', [
                EclipCaseStatus::SubmittedForEligibility->value,
                EclipCaseStatus::EligibilityReviewInProgress->value,
                EclipCaseStatus::Eligible->value,
                EclipCaseStatus::Ineligible->value,
                EclipCaseStatus::DocumentProcessing->value,
                EclipCaseStatus::DocumentsIncomplete->value,
                EclipCaseStatus::DocumentsCertified->value,
            ])
            ->with('formerRebel')
            ->latest('submitted_at')
            ->paginate(15);

        return view('lswdo.eclip.index', ['cases' => $cases]);
    }

    public function show(EclipCase $eclipCase): View
    {
        $this->authorize('view', $eclipCase);
        $eclipCase->load([
            'formerRebel.municipality', 'eligibilityReviews.reviewer', 'statusHistories.user',
            'documents.requirement', 'documents.versions.uploader', 'documents.latestVersion', 'documents.reviews.reviewer',
        ]);

        return view('lswdo.eclip.show', [
            'case' => $eclipCase,
            'requirements' => EclipDocumentRequirement::query()->where('is_active', true)->orderBy('sort_order')->get(),
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
        );

        return redirect()->route('lswdo.eclip.show', $eclipCase)->with('success', 'Eligibility decision recorded.');
    }
}
