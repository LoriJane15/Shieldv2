<?php

namespace App\Http\Controllers\Lswdo;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Models\EclipCase;
use App\Models\LswdoReferral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $referrals = LswdoReferral::query()->where('assigned_to', $user->id);
        $cases = EclipCase::query()->whereHas('participantAssignments', fn (Builder $query) => $query
            ->where('user_id', $user->id)
            ->where('participant_role', 'case_processor')
            ->where('is_active', true));
        $actionStatuses = [
            EclipCaseStatus::SubmittedForEligibility,
            EclipCaseStatus::EligibilityReviewInProgress,
            EclipCaseStatus::ReturnedForCorrection,
            EclipCaseStatus::AuthenticationReturned,
            EclipCaseStatus::DocumentsIncomplete,
        ];
        $actionStatusValues = collect($actionStatuses)->map->value;

        return view('lswdo.dashboard', [
            'summary' => [
                'pending_referrals' => (clone $referrals)->where('status', 'pending')->count(),
                'assigned_cases' => (clone $cases)->count(),
                'requiring_action' => (clone $cases)->whereIn('status', $actionStatusValues)->count(),
                'completed_cases' => (clone $cases)->where('status', EclipCaseStatus::Completed->value)->count(),
            ],
            'recentReferrals' => (clone $referrals)
                ->with(['formerRebel:id,classified_id', 'enrollment:id,integration_completed_at', 'eclipCase:id,lswdo_referral_id,case_number,status'])
                ->latest('referred_at')
                ->limit(5)
                ->get(),
            'actionCases' => (clone $cases)
                ->whereIn('status', $actionStatusValues)
                ->with('formerRebel:id,classified_id')
                ->oldest('updated_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
