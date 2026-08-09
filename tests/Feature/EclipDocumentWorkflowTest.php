<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipCase;
use App\Models\EclipDocument;
use App\Models\EclipDocumentRequirement;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EclipDocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_lswdo_upload_is_private_versioned_and_starts_document_processing(): void
    {
        Storage::fake('local');
        [$case, $mblrc, $lswdo] = $this->caseAndMblrc();
        $requirement = $this->requirement();

        foreach (['first.pdf', 'replacement.pdf'] as $filename) {
            $this->actingAs($lswdo)->post(route('lswdo.eclip.documents.store', $case), [
                'requirement_id' => $requirement->id,
                'document' => UploadedFile::fake()->create($filename, 50, 'application/pdf'),
            ])->assertRedirect();
        }

        $document = EclipDocument::query()->firstOrFail();
        $this->assertSame('pending', $document->status);
        $this->assertDatabaseCount('eclip_document_versions', 2);
        $this->assertSame([1, 2], $document->versions()->orderBy('version_number')->pluck('version_number')->all());
        $document->versions->each(fn ($version) => Storage::disk('local')->assertExists($version->storage_path));
        $this->assertSame(EclipCaseStatus::DocumentProcessing, $case->fresh()->status);
    }

    public function test_unsupported_document_type_is_rejected(): void
    {
        Storage::fake('local');
        [$case, $mblrc, $lswdo] = $this->caseAndMblrc();

        $this->actingAs($lswdo)->post(route('lswdo.eclip.documents.store', $case), [
            'requirement_id' => $this->requirement()->id,
            'document' => UploadedFile::fake()->create('program.exe', 10, 'application/x-msdownload'),
        ])->assertSessionHasErrors('document');

        $this->assertDatabaseCount('eclip_document_versions', 0);
    }

    public function test_japic_authenticates_then_certifies_all_required_documents(): void
    {
        Storage::fake('local');
        [$case, $mblrc, $lswdo] = $this->caseAndMblrc();
        $japic = User::factory()->role('japic')->create();
        $requirement = $this->requirement();
        $this->upload($case, $lswdo, $requirement);
        $document = EclipDocument::query()->firstOrFail();

        $this->actingAs($japic)->post(route('japic.eclip.documents.review', $document), [
            'decision' => 'authenticated',
        ])->assertRedirect();
        $this->assertSame('authenticated', $document->fresh()->status);

        $this->actingAs($japic)->post(route('japic.eclip.documents.review', $document), [
            'decision' => 'certified',
        ])->assertRedirect();

        $this->assertSame('certified', $document->fresh()->status);
        $this->assertSame(EclipCaseStatus::DocumentsCertified, $case->fresh()->status);
        $this->assertDatabaseCount('eclip_document_reviews', 2);
        $this->assertDatabaseHas('eclip_status_histories', [
            'eclip_case_id' => $case->id,
            'to_status' => EclipCaseStatus::DocumentsCertified->value,
            'user_id' => $japic->id,
        ]);
    }

    public function test_invalid_decision_requires_remarks_and_marks_case_incomplete(): void
    {
        Storage::fake('local');
        [$case, $mblrc, $lswdo] = $this->caseAndMblrc();
        $japic = User::factory()->role('japic')->create();
        $this->upload($case, $lswdo, $this->requirement());
        $document = EclipDocument::query()->firstOrFail();

        $this->actingAs($japic)->post(route('japic.eclip.documents.review', $document), [
            'decision' => 'invalid', 'remarks' => '',
        ])->assertSessionHasErrors('remarks');

        $this->actingAs($japic)->post(route('japic.eclip.documents.review', $document), [
            'decision' => 'invalid', 'remarks' => 'Synthetic test document is unreadable.',
        ])->assertRedirect();

        $this->assertSame('invalid', $document->fresh()->status);
        $this->assertSame(EclipCaseStatus::DocumentsIncomplete, $case->fresh()->status);
    }

    public function test_document_download_requires_an_authorized_role(): void
    {
        Storage::fake('local');
        [$case, $mblrc, $lswdo] = $this->caseAndMblrc();
        $this->upload($case, $lswdo, $this->requirement());
        $version = EclipDocument::query()->firstOrFail()->latestVersion()->firstOrFail();

        $this->actingAs(User::factory()->role('lgu')->create())
            ->get(route('mblrc.eclip.documents.download', $version))->assertForbidden();
        $this->actingAs($lswdo)
            ->get(route('lswdo.eclip.documents.download', $version))
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->actingAs($lswdo)
            ->get(route('lswdo.eclip.documents.preview', $version))
            ->assertOk()
            ->assertSee('Secure Document Preview')
            ->assertSee(route('lswdo.eclip.documents.download', $version), false);
    }

    public function test_japic_cannot_open_a_case_before_document_processing(): void
    {
        [$case] = $this->caseAndMblrc();

        $this->actingAs(User::factory()->role('japic')->create())
            ->get(route('japic.eclip.show', $case))->assertForbidden();
    }

    public function test_katuparan_admin_can_configure_requirements_with_audit_history(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)->post(route('admin.eclip.requirements.store'), [
            'code' => 'confirmed_test_requirement',
            'name' => 'Confirmed Test Requirement',
            'description' => 'Synthetic test configuration.',
            'is_required' => '1',
            'sort_order' => 10,
        ])->assertRedirect();

        $requirement = EclipDocumentRequirement::query()->firstOrFail();
        $this->assertSame('CONFIRMED_TEST_REQUIREMENT', $requirement->code);
        $this->assertDatabaseHas('eclip_document_requirement_histories', [
            'requirement_id' => $requirement->id,
            'user_id' => $admin->id,
            'action' => 'created',
        ]);

        $this->actingAs(User::factory()->role('mblrc')->create())
            ->post(route('admin.eclip.requirements.store'), [])->assertForbidden();
    }

    private function caseAndMblrc(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Document Test Municipality']);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => 'FR-#DOC1', 'firstname' => 'Synthetic', 'lastname' => 'Document',
            'municipality_id' => $municipality->id,
        ]);
        $mblrc = User::factory()->role('mblrc')->create();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-DOC-000001', 'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id, 'created_by' => $mblrc->id,
            'status' => EclipCaseStatus::Eligible,
        ]);
        $case->participantAssignments()->create([
            'user_id' => $lswdo->id,
            'participant_role' => 'case_processor',
            'assigned_by' => $mblrc->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        return [$case, $mblrc, $lswdo];
    }

    private function requirement(): EclipDocumentRequirement
    {
        return EclipDocumentRequirement::query()->create([
            'code' => 'SYNTHETIC_TEST_DOCUMENT', 'name' => 'Synthetic Test Document',
            'is_required' => true, 'is_active' => true,
        ]);
    }

    private function upload(EclipCase $case, User $lswdo, EclipDocumentRequirement $requirement): void
    {
        $this->actingAs($lswdo)->post(route('lswdo.eclip.documents.store', $case), [
            'requirement_id' => $requirement->id,
            'document' => UploadedFile::fake()->create('document.pdf', 50, 'application/pdf'),
        ])->assertRedirect();
    }
}
