<?php

namespace App\Http\Controllers\Lswdo;

use App\Http\Controllers\Controller;
use App\Models\LswdoReferral;
use App\Services\EclipOfficialWorkflowService;
use App\Services\MblrcReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $referrals = LswdoReferral::query()
            ->where('assigned_to', $request->user()->id)
            ->with(['formerRebel:id,classified_id', 'enrollment', 'eclipCase'])
            ->latest('referred_at')->paginate(20);

        return view('lswdo.referrals.index', compact('referrals'));
    }

    public function accept(
        Request $request,
        LswdoReferral $referral,
        MblrcReferralService $referrals,
        EclipOfficialWorkflowService $workflow,
    ): RedirectResponse {
        $case = $referrals->accept($referral, $request->user(), $workflow, $request->ip());

        return redirect()->route('lswdo.eclip.show', $case)
            ->with('success', 'Referral accepted and E-CLIP case initialized.');
    }
}
