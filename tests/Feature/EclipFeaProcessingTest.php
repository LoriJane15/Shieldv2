<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EclipFeaProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_pnp_can_upload_fea_record_and_assigned_lswdo_can_download_it(): void
    {
        Storage::fake('local');
        [$case, $pnp, $lswdo] = $this->caseWithParticipants();

        $this->actingAs($pnp)->post(route('pnp.eclip-fea.documents.store', $case), [
            'document_type' => 'ptis',
            'document' => UploadedFile::fake()->create('ptis.pdf', 50, 'application/pdf'),
        ])->assertRedirect();

        $document = $case->feaDocuments()->firstOrFail();
        $activity = $case->workflowActivities()->where('step_code', '4B')->firstOrFail();
        Storage::disk('local')->assertExists($document->storage_path);
        $this->assertSame('ongoing', $activity->fresh()->status);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $activity->id,
            'user_id' => $pnp->id,
            'event' => 'document_uploaded',
            'from_status' => 'pending',
            'to_status' => 'ongoing',
        ]);
        $this->assertSame($document->id, $activity->histories()->where('event', 'document_uploaded')->firstOrFail()->data['fea_document_id']);

        $this->actingAs($lswdo)
            ->get(route('eclip-fea.documents.download', $document))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_assigned_pnp_sees_the_structured_fea_case_workspace(): void
    {
        [$case, $pnp] = $this->caseWithParticipants();

        $this->actingAs($pnp)
            ->get(route('pnp.eclip-fea.show', $case))
            ->assertOk()
            ->assertSeeText('FEA Case Processing')
            ->assertSeeText('Pending Documents')
            ->assertSeeText('Case Progress')
            ->assertSeeText('Case Summary')
            ->assertSeeText('Drag and drop your file here')
            ->assertSeeText('Secure Document Upload')
            ->assertSeeText('0 of 3 completed')
            ->assertSeeText('Property Turn-In Slip (PTIS)')
            ->assertSeeText('Technical Inspection Report (TIR)')
            ->assertSeeText('Cost Valuation of Inventory Firearms (CVIF)')
            ->assertSeeText('No documents uploaded yet')
            ->assertSeeText('Upload First Document')
            ->assertSeeText('View Full Case Workflow')
            ->assertSeeText('Continue to Verification')
            ->assertDontSeeText('Complete FEA Verification');
    }

    public function test_fea_activity_cannot_be_completed_until_all_required_documents_are_uploaded(): void
    {
        [$case, $pnp] = $this->caseWithParticipants();
        $activity = $case->workflowActivities()->where('step_code', '4B')->firstOrFail();

        $this->actingAs($pnp)
            ->patch(route('eclip.workflow-activities.update', $activity), ['status' => 'completed'])
            ->assertRedirect()
            ->assertSessionHasErrors('document');

        $this->assertSame('pending', $activity->fresh()->status);
    }

    public function test_assigned_pnp_can_complete_fea_processing_after_all_required_documents_are_uploaded(): void
    {
        Storage::fake('local');
        [$case, $pnp] = $this->caseWithParticipants();
        $activity = $case->workflowActivities()->where('step_code', '4B')->firstOrFail();

        foreach (['ptis', 'tir', 'cvif'] as $type) {
            $this->actingAs($pnp)->post(route('pnp.eclip-fea.documents.store', $case), [
                'document_type' => $type,
                'document' => UploadedFile::fake()->create("{$type}.pdf", 50, 'application/pdf'),
            ])->assertRedirect();
        }

        $this->actingAs($pnp)
            ->get(route('pnp.eclip-fea.show', $case))
            ->assertOk()
            ->assertSeeText('Pending Verification')
            ->assertSeeText('3 of 3 completed')
            ->assertSeeText('Complete FEA Verification');

        $this->actingAs($pnp)
            ->patch(route('eclip.workflow-activities.update', $activity), ['status' => 'completed'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('completed', $activity->fresh()->status);
        $this->assertSame($pnp->id, $activity->fresh()->completed_by);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $activity->id,
            'user_id' => $pnp->id,
            'event' => 'status_changed',
            'from_status' => 'ongoing',
            'to_status' => 'completed',
        ]);
    }

    public function test_completed_fea_activity_rejects_additional_uploads(): void
    {
        Storage::fake('local');
        [$case, $pnp] = $this->caseWithParticipants();
        $case->workflowActivities()->where('step_code', '4B')->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $pnp->id,
        ]);

        $this->actingAs($pnp)->post(route('pnp.eclip-fea.documents.store', $case), [
            'document_type' => 'other',
            'document' => UploadedFile::fake()->create('late-upload.pdf', 50, 'application/pdf'),
        ])->assertForbidden();

        $this->assertDatabaseCount('eclip_fea_documents', 0);
    }

    public function test_unassigned_pnp_cannot_open_or_upload_to_an_fea_case(): void
    {
        [$case] = $this->caseWithParticipants();
        $unassignedPnp = User::factory()->role('pnp')->create();

        $this->actingAs($unassignedPnp)->get(route('pnp.eclip-fea.show', $case))->assertForbidden();
        $this->actingAs($unassignedPnp)->post(route('pnp.eclip-fea.documents.store', $case), [
            'document_type' => 'ptis',
            'document' => UploadedFile::fake()->create('ptis.pdf', 50, 'application/pdf'),
        ])->assertForbidden();
    }

    public function test_assigned_lswdo_can_reassign_fea_processor_with_history_and_notification(): void
    {
        [$case, $pnp, $lswdo] = $this->caseWithParticipants();
        $afp = User::factory()->role('afp')->create();

        $this->actingAs($lswdo)->post(route('lswdo.eclip.fea-processor.store', $case), [
            'processor_id' => $afp->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('eclip_case_participants', [
            'eclip_case_id' => $case->id,
            'user_id' => $pnp->id,
            'participant_role' => 'fea_processor',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('eclip_case_participants', [
            'eclip_case_id' => $case->id,
            'user_id' => $afp->id,
            'participant_role' => 'fea_processor',
            'is_active' => true,
            'assigned_by' => $lswdo->id,
        ]);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'user_id' => $lswdo->id,
            'remarks' => 'FEA processor assigned.',
        ]);
        $this->assertCount(1, $afp->notifications()->get());
    }

    private function caseWithParticipants(): array
    {
        $municipality = Municipality::query()->create(['name' => 'FEA Test Municipality']);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => 'FR-FEA-001', 'firstname' => 'Synthetic', 'lastname' => 'FEA',
            'municipality_id' => $municipality->id,
        ]);
        $mblrc = User::factory()->role('mblrc')->create();
        $pnp = User::factory()->role('pnp')->create();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-FEA-000001', 'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id, 'created_by' => $mblrc->id,
            'status' => EclipCaseStatus::Eligible,
        ]);
        foreach ([$pnp, $lswdo] as $participant) {
            $case->participantAssignments()->create([
                'user_id' => $participant->id,
                'participant_role' => $participant->role === 'pnp' ? 'fea_processor' : 'case_processor',
                'assigned_by' => $mblrc->id,
                'assigned_at' => now(),
                'is_active' => true,
            ]);
        }
        $case->workflowActivities()->create([
            'step_code' => '4B', 'phase' => 2, 'title' => 'FEA Processing',
            'status' => 'pending', 'responsible_roles' => ['pnp', 'afp'], 'available_at' => now(),
        ]);

        return [$case, $pnp, $lswdo];
    }
}
