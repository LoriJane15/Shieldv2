<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EclipReintegrationRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_lswdo_records_repeatable_reintegration_plan_items_with_step_history(): void
    {
        [$case, $lswdo] = $this->caseWithProcessor();
        $activity = $case->workflowActivities()->create([
            'step_code' => '10',
            'phase' => 4,
            'title' => 'Prepare FRRP/FVERP and Business Plan',
            'status' => 'pending',
            'responsible_roles' => ['lswdo'],
            'available_at' => now(),
        ]);

        $this->actingAs($lswdo)->post(route('lswdo.eclip.reintegration-plan-items.store', $case), [
            'identified_need' => 'Stable livelihood after community transition.',
            'proposed_assistance' => 'Agricultural starter package',
            'responsible_agency' => 'Authorized Partner Agency',
            'lgu_counterpart' => 'Municipal Agriculture Office',
            'form_of_assistance' => 'Equipment and training',
            'amount' => '25000.00',
            'target_date' => now()->addMonth()->toDateString(),
            'status' => 'planned',
            'partner_agencies' => 'Authorized Partner Agency',
            'agency_commitments' => 'Provide equipment and technical training.',
        ])->assertRedirect();

        $item = $case->reintegrationPlanItems()->firstOrFail();
        $this->assertSame('ongoing', $activity->fresh()->status);
        $this->assertDatabaseHas('eclip_reintegration_plan_item_histories', [
            'plan_item_id' => $item->id,
            'user_id' => $lswdo->id,
            'action' => 'created',
        ]);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $activity->id,
            'event' => 'plan_item_created',
        ]);
    }

    public function test_alternate_beneficiary_name_is_encrypted_and_step_12_event_is_audited(): void
    {
        [$case, $lswdo] = $this->caseWithProcessor();
        $activity = $case->workflowActivities()->create([
            'step_code' => '12',
            'phase' => 4,
            'title' => 'Provide Livelihood Assistance through an Identified Beneficiary',
            'status' => 'pending',
            'responsible_roles' => ['lswdo'],
            'available_at' => now(),
        ]);

        $this->actingAs($lswdo)->post(route('lswdo.eclip.livelihood-assistances.store', $case), [
            'implementation_reason' => 'The FR/FVE cannot personally operate the approved project.',
            'beneficiary_name' => 'Synthetic Authorized Beneficiary',
            'relationship' => 'Authorized household member',
            'approval_reference' => 'APPROVAL-STEP12-001',
            'approval_status' => 'approved',
            'assistance_amount' => '15000.00',
            'release_status' => 'released',
            'release_date' => now()->toDateString(),
            'supporting_reference' => 'SUPPORT-STEP12-001',
            'remarks' => 'Release acknowledged.',
        ])->assertRedirect();

        $assistance = $case->livelihoodBeneficiaryAssistances()->firstOrFail();
        $this->assertSame('Synthetic Authorized Beneficiary', $assistance->beneficiary_name);
        $this->assertNotSame('Synthetic Authorized Beneficiary', $assistance->getRawOriginal('beneficiary_name'));
        $this->assertSame('ongoing', $activity->fresh()->status);
        $this->assertDatabaseHas('eclip_livelihood_beneficiary_assistance_histories', [
            'assistance_id' => $assistance->id,
            'action' => 'created',
        ]);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $activity->id,
            'event' => 'livelihood_beneficiary_recorded',
        ]);
    }

    public function test_unassigned_lswdo_cannot_write_reintegration_records(): void
    {
        [$case] = $this->caseWithProcessor();
        $outside = User::factory()->role('lswdo')->create(['municipality_id' => $case->municipality_id]);

        $this->actingAs($outside)->post(route('lswdo.eclip.reintegration-plan-items.store', $case), [
            'identified_need' => 'Unauthorized',
            'proposed_assistance' => 'Unauthorized',
            'responsible_agency' => 'Unauthorized',
            'form_of_assistance' => 'Unauthorized',
            'target_date' => now()->toDateString(),
            'status' => 'planned',
        ])->assertForbidden();
    }

    private function caseWithProcessor(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Reintegration Municipality']);
        $mblrc = User::factory()->role('mblrc')->create();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => 'FR-REINTEGRATION-001',
            'firstname' => 'Synthetic',
            'lastname' => 'Reintegration',
            'municipality_id' => $municipality->id,
        ]);
        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-REINTEGRATION-001',
            'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id,
            'created_by' => $mblrc->id,
            'assigned_to' => $lswdo->id,
            'status' => EclipCaseStatus::AssistanceReleased,
        ]);
        $case->participantAssignments()->create([
            'user_id' => $lswdo->id,
            'participant_role' => 'case_processor',
            'assigned_by' => $mblrc->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        return [$case, $lswdo];
    }
}
