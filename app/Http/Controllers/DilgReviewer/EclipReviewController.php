<?php

namespace App\Http\Controllers\DilgReviewer;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DilgReviewer\DecideEclipReviewRequest;
use App\Models\EclipCase;
use App\Models\User;
use App\Services\EclipDilgReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EclipReviewController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $activeStatus = match ($user->role) {
            'dilg_regional' => EclipCaseStatus::ProvincialEndorsed,
            'nboo_eclip_pmo' => EclipCaseStatus::RegionalEndorsed,
            default => EclipCaseStatus::SubmittedForDilgReview,
        };

        $cases = EclipCase::query()
            ->when($user->hasRole('dilg_provincial_focal', 'dilg_reviewer'), fn ($query) => $query->where('municipality_id', $user->municipality_id))
            ->whereIn('status', [
                $activeStatus->value,
                EclipCaseStatus::ReturnedForAssessmentRevision->value,
                EclipCaseStatus::Approved->value,
                EclipCaseStatus::Rejected->value,
            ])
            ->with(['formerRebel', 'assistanceRequest.latestRevision.category'])
            ->latest('updated_at')->paginate(15);

        return view('dilg_reviewer.cases.index', ['cases' => $cases, 'reviewLevel' => $this->reviewLevel($user)]);
    }

    public function show(EclipCase $eclipCase): View
    {
        $this->authorize('view', $eclipCase);
        $eclipCase->load([
            'formerRebel.municipality', 'assistanceRequest.revisions.category',
            'assistanceRequest.revisions.creator', 'dilgReviews.reviewer', 'dilgReviews.revision', 'statusHistories.user',
        ]);

        return view('dilg_reviewer.cases.show', [
            'case' => $eclipCase,
            'reviewLevel' => $this->reviewLevel(request()->user()),
            'positiveDecision' => request()->user()->hasRole('dilg_provincial_focal', 'dilg_regional') ? 'endorsed' : 'approved',
        ]);
    }

    public function decide(
        DecideEclipReviewRequest $request,
        EclipCase $eclipCase,
        EclipDilgReviewService $reviews,
    ): RedirectResponse {
        $reviews->decide(
            $eclipCase, $request->user(), $request->validated('decision'),
            $request->validated('feedback'), $request->ip(),
        );

        return back()->with('success', 'DILG review decision recorded.');
    }

    private function reviewLevel(User $user): string
    {
        return match ($user->role) {
            'dilg_provincial_focal' => 'Provincial',
            'dilg_regional' => 'Regional',
            'nboo_eclip_pmo' => 'National',
            default => 'DILG',
        };
    }
}
