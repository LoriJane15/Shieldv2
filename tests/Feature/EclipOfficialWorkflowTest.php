<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\Barangay;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use App\Services\EclipWorkflowPresentationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EclipOfficialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_initializes_the_complete_official_workflow(): void
    {
        [$case, $mblrc] = $this->case();

        $this->actingAs($mblrc)->post(route('mblrc.eclip.submit', $case))->assertRedirect();

        $this->assertDatabaseCount('eclip_workflow_activities', count(config('eclip_workflow.steps')));
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '1', 'status' => 'completed']);
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '2', 'status' => 'completed']);
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '3A', 'status' => 'pending']);
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '14', 'status' => 'locked']);
    }

    public function test_eligible_decision_completes_step_3a_and_unlocks_3b(): void
    {
        [$case, $mblrc, $municipality] = $this->case();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $this->actingAs($mblrc)->post(route('mblrc.eclip.submit', $case));

        $this->actingAs($lswdo)->post(route('lswdo.eclip.eligibility.decide', $case), ['decision' => 'eligible'])->assertRedirect();

        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '3A', 'status' => 'completed']);
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '3B', 'status' => 'pending']);
    }

    public function test_only_the_responsible_scoped_office_can_update_an_activity(): void
    {
        [$case, $mblrc, $municipality] = $this->case();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $outsideMunicipality = Municipality::query()->create(['name' => 'Outside Municipality']);
        $outsideLswdo = User::factory()->role('lswdo')->create(['municipality_id' => $outsideMunicipality->id]);
        $this->actingAs($mblrc)->post(route('mblrc.eclip.submit', $case));
        $this->actingAs($lswdo)->post(route('lswdo.eclip.eligibility.decide', $case), ['decision' => 'eligible']);
        $activity = $case->workflowActivities()->where('step_code', '3B')->firstOrFail();

        $this->actingAs($outsideLswdo)->patch(route('eclip.workflow-activities.update', $activity), ['status' => 'completed'])->assertForbidden();
        $this->actingAs($lswdo)->patch(route('eclip.workflow-activities.update', $activity), ['status' => 'completed'])->assertRedirect();

        $this->assertSame('completed', $activity->fresh()->status);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', ['activity_id' => $activity->id, 'user_id' => $lswdo->id, 'to_status' => 'completed']);
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '4A', 'status' => 'pending']);
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '4B', 'status' => 'pending']);
    }

    public function test_workflow_presentation_uses_real_next_step_ownership_and_dependencies(): void
    {
        [$case, $mblrc, $municipality] = $this->case();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $this->actingAs($mblrc)->post(route('mblrc.eclip.submit', $case));
        $this->actingAs($lswdo)->post(route('lswdo.eclip.eligibility.decide', $case), ['decision' => 'eligible']);

        $step3b = $case->workflowActivities()->where('step_code', '3B')->firstOrFail();
        $this->actingAs($lswdo)->patch(route('eclip.workflow-activities.update', $step3b), ['status' => 'completed']);

        $case->load(['workflowActivities', 'statusHistories']);
        $presentation = app(EclipWorkflowPresentationService::class)->forCase($case, $lswdo);
        $step4c = $case->workflowActivities->firstWhere('step_code', '4C');

        $this->assertSame('4A', $presentation['next_activity']->step_code);
        $this->assertSame(['JAPIC'], $presentation['next_meta']['responsible']);
        $this->assertFalse($presentation['next_meta']['can_update']);
        $this->assertSame(['4A'], $presentation['activity_meta']->get($step4c->id)['blocking_dependencies']->pluck('code')->all());
        $this->assertSame(2, $presentation['current_phase']);
    }

    private function case(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Workflow Municipality']);
        $barangay = Barangay::query()->create(['municipality_id' => $municipality->id, 'name' => 'Workflow Barangay']);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => 'FR-WORKFLOW-001', 'firstname' => 'Synthetic', 'lastname' => 'Beneficiary',
            'municipality_id' => $municipality->id, 'barangay_id' => $barangay->id,
        ]);
        $mblrc = User::factory()->role('mblrc')->create();
        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-WORKFLOW-001', 'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id, 'created_by' => $mblrc->id, 'status' => EclipCaseStatus::Draft,
        ]);

        return [$case, $mblrc, $municipality];
    }
}
