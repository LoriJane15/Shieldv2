<?php

namespace App\Http\Controllers\Mblrc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mblrc\CompleteEnrollmentRequest;
use App\Http\Requests\Mblrc\StoreEnrollmentRequest;
use App\Models\FormerRebel;
use App\Models\MblrcEnrollment;
use App\Models\Municipality;
use App\Services\MblrcReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $enrollments = MblrcEnrollment::query()
            ->where('assigned_user_id', $request->user()->id)
            ->with(['formerRebel', 'verifiedMunicipality', 'referral.assignee'])
            ->latest()->paginate(20);

        $availableBeneficiaries = FormerRebel::query()
            ->whereDoesntHave('mblrcEnrollment')
            ->orderBy('classified_id')
            ->get(['id', 'classified_id']);

        return view('mblrc.enrollments.index', [
            'enrollments' => $enrollments,
            'availableBeneficiaries' => $availableBeneficiaries,
            'municipalities' => Municipality::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreEnrollmentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $enrollment = MblrcEnrollment::query()->firstOrCreate(
            ['former_rebel_id' => $data['former_rebel_id']],
            [
                'assigned_user_id' => $request->user()->id,
                'created_by' => $request->user()->id,
                'status' => 'in_progress',
                'integration_started_at' => $data['integration_started_at'],
            ],
        );

        abort_unless($enrollment->assigned_user_id === $request->user()->id, 403);

        return back()->with('success', 'Integration enrollment is now in progress.');
    }

    public function complete(
        CompleteEnrollmentRequest $request,
        MblrcEnrollment $enrollment,
        MblrcReferralService $referrals,
    ): RedirectResponse {
        $referral = $referrals->completeIntegration($enrollment, $request->validated(), $request->user());

        return back()->with('success', $referral->assigned_to
            ? 'Integration completed and assigned LSWDO referral created.'
            : 'Integration completed. The referral is pending LSWDO assignment.');
    }
}
