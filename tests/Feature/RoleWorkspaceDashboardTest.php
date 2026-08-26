<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipAuthenticationRequest;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\LswdoReferral;
use App\Models\MblrcEnrollment;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleWorkspaceDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_lswdo_dashboard_is_assignment_scoped(): void
    {
        $municipality = Municipality::query()->create(['name' => 'LSWDO Dashboard Municipality']);
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $outsideLswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $mblrc = User::factory()->role('mblrc')->create();
        $assignedBeneficiary = $this->beneficiary($municipality, 'FR-#LSWDO-ASSIGNED');
        $outsideBeneficiary = $this->beneficiary($municipality, 'FR-#LSWDO-OUTSIDE');
        $assignedReferral = $this->referral($assignedBeneficiary, $mblrc, $lswdo);
        $this->referral($outsideBeneficiary, $mblrc, $outsideLswdo);
        $assignedCase = $this->case($assignedBeneficiary, $municipality, $mblrc, EclipCaseStatus::SubmittedForEligibility);
        $assignedCase->update(['lswdo_referral_id' => $assignedReferral->id]);
        $this->assign($assignedCase, $lswdo, $mblrc);
        $outsideCase = $this->case($outsideBeneficiary, $municipality, $mblrc, EclipCaseStatus::SubmittedForEligibility);
        $this->assign($outsideCase, $outsideLswdo, $mblrc);

        $this->actingAs($lswdo)
            ->get(route('lswdo.dashboard'))
            ->assertOk()
            ->assertSeeText('Assigned Intake and E-CLIP Workload')
            ->assertSeeText('FR-#LSWDO-ASSIGNED')
            ->assertDontSeeText('FR-#LSWDO-OUTSIDE')
            ->assertViewHas('summary', [
                'pending_referrals' => 1,
                'assigned_cases' => 1,
                'requiring_action' => 1,
                'completed_cases' => 0,
            ]);

        $this->assertSame('lswdo.dashboard', $lswdo->homeRoute());
    }

    public function test_japic_dashboard_uses_only_assigned_step_4a_work(): void
    {
        $municipality = Municipality::query()->create(['name' => 'JAPIC Dashboard Municipality']);
        $japic = User::factory()->role('japic')->create();
        $outsideJapic = User::factory()->role('japic')->create();
        $mblrc = User::factory()->role('mblrc')->create();
        $assignedBeneficiary = $this->beneficiary($municipality, 'FR-#JAPIC-ASSIGNED');
        $outsideBeneficiary = $this->beneficiary($municipality, 'FR-#JAPIC-OUTSIDE');
        $assignedCase = $this->case($assignedBeneficiary, $municipality, $mblrc, EclipCaseStatus::AuthenticationPending);
        $outsideCase = $this->case($outsideBeneficiary, $municipality, $mblrc, EclipCaseStatus::AuthenticationPending);
        $this->assign($assignedCase, $japic, $mblrc, 'authentication_reviewer');
        $this->assign($outsideCase, $outsideJapic, $mblrc, 'authentication_reviewer');
        EclipAuthenticationRequest::query()->create([
            'eclip_case_id' => $assignedCase->id,
            'requested_by' => $mblrc->id,
            'assigned_to' => $japic->id,
            'status' => 'pending',
            'requested_at' => now(),
            'due_at' => now()->addDays(10),
        ]);
        EclipAuthenticationRequest::query()->create([
            'eclip_case_id' => $outsideCase->id,
            'requested_by' => $mblrc->id,
            'assigned_to' => $outsideJapic->id,
            'status' => 'pending',
            'requested_at' => now(),
            'due_at' => now()->addDays(10),
        ]);

        $this->actingAs($japic)
            ->get(route('japic.dashboard'))
            ->assertOk()
            ->assertSeeText('Official Workflow · Step 4A')
            ->assertSeeText('FR-#JAPIC-ASSIGNED')
            ->assertDontSeeText('FR-#JAPIC-OUTSIDE')
            ->assertViewHas('summary', [
                'pending' => 1,
                'under_review' => 0,
                'overdue' => 0,
                'document_cases' => 0,
            ]);

        $this->assertSame('japic.dashboard', $japic->homeRoute());
    }

    public function test_local_committee_register_is_municipality_scoped_and_does_not_expose_names(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Committee Municipality']);
        $outsideMunicipality = Municipality::query()->create(['name' => 'Outside Committee Municipality']);
        $committee = User::factory()->role('local_eclip_committee')->create(['municipality_id' => $municipality->id]);
        $mblrc = User::factory()->role('mblrc')->create();
        $visible = $this->beneficiary($municipality, 'FR-#VISIBLE', 'Sensitive Visible Name');
        $visible->update(['surrender_date' => '2026-06-15', 'placement_address' => 'Fallback Location']);
        $outside = $this->beneficiary($outsideMunicipality, 'FR-#OUTSIDE', 'Sensitive Outside Name');
        $case = $this->case($visible, $municipality, $mblrc, EclipCaseStatus::Eligible);
        $case->workflowActivities()->create([
            'step_code' => '1',
            'phase' => 1,
            'title' => 'Signifies Intention to Surface',
            'status' => 'completed',
            'responsible_roles' => ['mblrc'],
            'data' => ['intention_date' => '2026-06-20', 'receiving_unit' => 'Municipal Receiving Center'],
        ]);
        $this->case($outside, $outsideMunicipality, $mblrc, EclipCaseStatus::Eligible);

        $this->actingAs($committee)
            ->get(route('local_eclip.surfaced.index'))
            ->assertOk()
            ->assertSeeText('Surfaced FR/FVE Register')
            ->assertSeeText('FR-#VISIBLE')
            ->assertSeeText('Jun 20, 2026')
            ->assertSeeText('Municipal Receiving Center')
            ->assertSeeText($case->case_number)
            ->assertSeeText('Eligible')
            ->assertDontSeeText('Sensitive Visible Name')
            ->assertDontSeeText('FR-#OUTSIDE')
            ->assertDontSeeText('Sensitive Outside Name');

        $this->actingAs(User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]))
            ->get(route('local_eclip.surfaced.index'))
            ->assertForbidden();
    }

    private function beneficiary(Municipality $municipality, string $classifiedId, string $firstName = 'Synthetic'): FormerRebel
    {
        return FormerRebel::query()->create([
            'classified_id' => $classifiedId,
            'firstname' => $firstName,
            'lastname' => 'Beneficiary',
            'municipality_id' => $municipality->id,
        ]);
    }

    private function referral(FormerRebel $beneficiary, User $mblrc, User $lswdo): LswdoReferral
    {
        $enrollment = MblrcEnrollment::query()->create([
            'former_rebel_id' => $beneficiary->id,
            'assigned_user_id' => $mblrc->id,
            'created_by' => $mblrc->id,
            'status' => 'completed',
            'integration_started_at' => '2026-01-01',
            'integration_completed_at' => '2026-04-01',
        ]);

        return LswdoReferral::query()->create([
            'referral_number' => 'REF-'.$beneficiary->id,
            'mblrc_enrollment_id' => $enrollment->id,
            'former_rebel_id' => $beneficiary->id,
            'municipality_id' => $beneficiary->municipality_id,
            'assigned_to' => $lswdo->id,
            'created_by' => $mblrc->id,
            'status' => 'pending',
            'referred_at' => now(),
        ]);
    }

    private function case(FormerRebel $beneficiary, Municipality $municipality, User $creator, EclipCaseStatus $status): EclipCase
    {
        return EclipCase::query()->create([
            'case_number' => 'ECLIP-'.$beneficiary->id,
            'former_rebel_id' => $beneficiary->id,
            'municipality_id' => $municipality->id,
            'created_by' => $creator->id,
            'status' => $status,
            'submitted_at' => now(),
        ]);
    }

    private function assign(EclipCase $case, User $user, User $assigner, string $role = 'case_processor'): void
    {
        $case->participantAssignments()->create([
            'user_id' => $user->id,
            'participant_role' => $role,
            'assigned_by' => $assigner->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);
    }
}
