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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '2', 'status' => 'pending']);
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '3A', 'status' => 'locked']);
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '14', 'status' => 'locked']);
    }

    public function test_eligible_decision_completes_step_3a_and_unlocks_3b(): void
    {
        [$case, $mblrc, , $lswdo] = $this->case();
        $this->actingAs($mblrc)->post(route('mblrc.eclip.submit', $case));
        $this->completeStep2($case, $lswdo);

        $this->actingAs($lswdo)->post(route('lswdo.eclip.eligibility.decide', $case), ['decision' => 'eligible'])->assertRedirect();

        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '3A', 'status' => 'completed']);
        $this->assertDatabaseHas('eclip_workflow_activities', ['eclip_case_id' => $case->id, 'step_code' => '3B', 'status' => 'pending']);
    }

    public function test_only_the_responsible_scoped_office_can_update_an_activity(): void
    {
        [$case, $mblrc, $municipality, $lswdo] = $this->case();
        $outsideMunicipality = Municipality::query()->create(['name' => 'Outside Municipality']);
        $outsideLswdo = User::factory()->role('lswdo')->create(['municipality_id' => $outsideMunicipality->id]);
        $this->actingAs($mblrc)->post(route('mblrc.eclip.submit', $case));
        $this->completeStep2($case, $lswdo);
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
        [$case, $mblrc, , $lswdo] = $this->case();
        $this->actingAs($mblrc)->post(route('mblrc.eclip.submit', $case));
        $this->completeStep2($case, $lswdo);
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

    public function test_lswdo_step_updates_store_only_configured_monitoring_fields_in_immutable_history(): void
    {
        [$case, , , $lswdo] = $this->case();
        $activity = $case->workflowActivities()->create([
            'step_code' => '4D',
            'phase' => 2,
            'title' => 'Provide Social Protection Services',
            'status' => 'pending',
            'responsible_roles' => ['lswdo'],
            'available_at' => now(),
        ]);

        $this->actingAs($lswdo)->patch(route('eclip.workflow-activities.update', $activity), [
            'status' => 'completed',
            'remarks' => 'Synthetic monitoring update.',
            'data' => [
                'service_name' => 'Counseling',
                'service_date' => '2026-08-24',
                'service_provider' => 'Synthetic LSWDO',
                'unapproved_key' => 'Must not be stored',
            ],
        ])->assertRedirect();

        $this->assertSame([
            'service_name' => 'Counseling',
            'service_date' => '2026-08-24',
            'service_provider' => 'Synthetic LSWDO',
        ], $activity->fresh()->data);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $activity->id,
            'user_id' => $lswdo->id,
            'to_status' => 'completed',
            'remarks' => 'Synthetic monitoring update.',
        ]);
    }

    public function test_same_municipality_lswdo_cannot_update_a_case_assigned_to_another_processor(): void
    {
        [$case, , $municipality] = $this->case();
        $unassignedLswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $activity = $case->workflowActivities()->create([
            'step_code' => '4D',
            'phase' => 2,
            'title' => 'Provide Social Protection Services',
            'status' => 'pending',
            'responsible_roles' => ['lswdo'],
            'available_at' => now(),
        ]);

        $this->actingAs($unassignedLswdo)
            ->patch(route('eclip.workflow-activities.update', $activity), ['status' => 'completed'])
            ->assertForbidden();

        $this->assertSame('pending', $activity->fresh()->status);
    }

    public function test_required_step_evidence_is_private_versioned_and_required_before_completion(): void
    {
        Storage::fake('local');
        [$case, , , $lswdo] = $this->case();
        $activity = $case->workflowActivities()->create([
            'step_code' => '4C',
            'phase' => 2,
            'title' => 'Accomplish E-CLIP Enrollment Form',
            'status' => 'pending',
            'responsible_roles' => ['lswdo'],
            'required_documents' => ['E-CLIP Enrollment Form (Form 2)'],
            'available_at' => now(),
        ]);

        $this->actingAs($lswdo)
            ->patch(route('eclip.workflow-activities.update', $activity), ['status' => 'completed'])
            ->assertSessionHasErrors('document');

        $this->actingAs($lswdo)->post(route('eclip.workflow-documents.store', $activity), [
            'document_type' => 'E-CLIP Enrollment Form (Form 2)',
            'document' => UploadedFile::fake()->create('enrollment.pdf', 64, 'application/pdf'),
        ])->assertRedirect();

        $document = $activity->documents()->firstOrFail();
        Storage::disk('local')->assertExists($document->storage_path);
        $this->assertSame(1, $document->version_number);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $activity->id,
            'event' => 'document_uploaded',
            'actor_role' => 'lswdo',
            'document_id' => $document->id,
        ]);

        $this->actingAs($lswdo)
            ->patch(route('eclip.workflow-activities.update', $activity), ['status' => 'completed'])
            ->assertRedirect();
        $this->actingAs($lswdo)->get(route('eclip.workflow-documents.download', $document))->assertOk();
    }

    public function test_corrected_prerequisite_resubmits_a_returned_review_step(): void
    {
        [$case, , $municipality, $lswdo] = $this->case();
        $provincial = User::factory()->role('dilg_provincial_focal')->create(['municipality_id' => $municipality->id]);
        $previous = $case->workflowActivities()->create([
            'step_code' => '6C', 'phase' => 3, 'title' => 'Monitor Claims', 'status' => 'completed',
            'responsible_roles' => ['lswdo'], 'completed_at' => now(), 'completed_by' => $lswdo->id,
        ]);
        $review = $case->workflowActivities()->create([
            'step_code' => '6D', 'phase' => 3, 'title' => 'Provincial Review', 'status' => 'pending',
            'responsible_roles' => ['dilg_provincial_focal'], 'available_at' => now(),
        ]);

        $this->actingAs($provincial)->patch(route('eclip.workflow-activities.update', $review), [
            'status' => 'returned_for_correction', 'remarks' => 'Correct the claim reference.',
        ])->assertRedirect();
        $this->assertSame('pending', $previous->fresh()->status);

        $this->actingAs($lswdo)->patch(route('eclip.workflow-activities.update', $previous), ['status' => 'completed'])->assertRedirect();

        $this->assertSame('pending', $review->fresh()->status);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $review->id, 'event' => 'resubmitted', 'from_status' => 'returned_for_correction', 'to_status' => 'pending',
        ]);
    }

    public function test_assigned_personnel_can_open_the_shared_workflow_but_unassigned_personnel_cannot(): void
    {
        [$case, , $municipality, $lswdo] = $this->case();
        $case->workflowActivities()->create([
            'step_code' => '2', 'phase' => 1, 'title' => 'Notify Local E-CLIP Committee',
            'status' => 'pending', 'responsible_roles' => ['lswdo'], 'available_at' => now(),
        ]);
        $unassigned = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);

        $this->actingAs($lswdo)
            ->get(route('eclip.workflow.show', $case))
            ->assertOk()
            ->assertSee('Official E-CLIP and Amnesty Program Tracker')
            ->assertSee('Action required by your office');

        $this->actingAs($unassigned)->get(route('eclip.workflow.show', $case))->assertForbidden();
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

        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $case->participantAssignments()->create([
            'user_id' => $lswdo->id,
            'participant_role' => 'case_processor',
            'assigned_by' => $mblrc->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        return [$case, $mblrc, $municipality, $lswdo];
    }

    private function completeStep2(EclipCase $case, User $lswdo): void
    {
        $activity = $case->workflowActivities()->where('step_code', '2')->firstOrFail();
        $this->actingAs($lswdo)
            ->patch(route('eclip.workflow-activities.update', $activity), ['status' => 'completed'])
            ->assertRedirect();
    }
}
