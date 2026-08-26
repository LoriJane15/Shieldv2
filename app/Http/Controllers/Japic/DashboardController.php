<?php

namespace App\Http\Controllers\Japic;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Models\EclipAuthenticationRequest;
use App\Models\EclipCase;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $authenticationRequests = EclipAuthenticationRequest::query()->where('assigned_to', $user->id);
        $documentCases = EclipCase::query()
            ->whereHas('participantAssignments', fn ($query) => $query
                ->where('user_id', $user->id)
                ->where('participant_role', 'authentication_reviewer')
                ->where('is_active', true))
            ->whereIn('status', [
                EclipCaseStatus::DocumentProcessing->value,
                EclipCaseStatus::DocumentsIncomplete->value,
                EclipCaseStatus::DocumentsCertified->value,
            ]);

        return view('japic.dashboard', [
            'summary' => [
                'pending' => (clone $authenticationRequests)->where('status', 'pending')->count(),
                'under_review' => (clone $authenticationRequests)->where('status', 'under_review')->count(),
                'overdue' => (clone $authenticationRequests)->whereIn('status', ['pending', 'under_review'])->where('due_at', '<', now())->count(),
                'document_cases' => (clone $documentCases)->count(),
            ],
            'recentRequests' => (clone $authenticationRequests)
                ->with('eclipCase.formerRebel:id,classified_id')
                ->latest('requested_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
