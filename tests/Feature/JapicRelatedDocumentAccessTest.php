<?php

namespace Tests\Feature;

use App\Enums\Ib39FeaUploadSlot;
use App\Enums\Ib39FrCategory;
use App\Enums\JapicCertificationStatus;
use App\Models\Ib39FeaDocumentVersion;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\JapicCertificationProcessing;
use App\Models\User;
use App\Services\Ib39SurfacedFormerRebelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JapicRelatedDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_japic_can_read_only_current_final_cdr_and_missing_files_are_not_found(): void
    {
        Storage::fake('local');
        [$japic, $record, $processing, $current] = $this->context(false);
        $cdr = $record->cdrProcessing;
        Storage::disk('local')->put($current->getRawOriginal('storage_path'), '%PDF-1.4 current');

        $this->actingAs($japic)->get(route('japic.certifications.records.cdr', $processing))
            ->assertOk()->assertSee('current authoritative final CDR')->assertSee('Secure preview')
            ->assertDontSee('CDR History')->assertDontSee('private/cdr');
        $this->actingAs($japic)->get(route('japic.cdr.documents.preview', [$cdr, $current]))->assertOk();
        $this->actingAs($japic)->get(route('japic.cdr.documents.download', [$cdr, $current]))->assertOk();

        $oldId = DB::table('ib39_cdr_document_versions')->insertGetId(['cdr_processing_id' => $cdr->id, 'version_number' => 2,
            'source_type' => 'uploaded', 'storage_path' => 'private/cdr/old.pdf', 'original_filename' => 'old.pdf', 'mime_type' => 'application/pdf',
            'size_bytes' => 1, 'sha256' => str_repeat('d', 64), 'created_by' => $japic->id, 'finalized_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $old = $cdr->documentVersions()->findOrFail($oldId);
        $this->actingAs($japic)->get(route('japic.cdr.documents.preview', [$cdr, $old]))->assertForbidden();

        Storage::disk('local')->delete($current->getRawOriginal('storage_path'));
        $this->actingAs($japic)->get(route('japic.cdr.documents.preview', [$cdr, $current]))->assertNotFound();
        $this->assertNotNull($processing);
    }

    public function test_fea_access_enforces_processing_document_version_and_fr_ownership(): void
    {
        Storage::fake('local');
        [$japic, $record, $processing] = $this->context(true);
        $fea = $record->feaProcessing;
        $document = $fea->documents->first();
        $version = Ib39FeaDocumentVersion::query()->forceCreate(['fea_processing_id' => $fea->id, 'fea_document_id' => $document->id,
            'slot' => Ib39FeaUploadSlot::Primary, 'version_number' => 1, 'storage_path' => "ib39/fea/{$fea->id}/test.pdf",
            'original_filename' => 'evidence.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 1, 'sha256' => str_repeat('e', 64), 'uploaded_by' => $japic->id]);
        $document->update(['current_draft_version_id' => $version->id]);
        Storage::disk('local')->put($version->getRawOriginal('storage_path'), '%PDF-1.4 fea');

        $this->actingAs($japic)->get(route('japic.certifications.records.fea', $processing))
            ->assertOk()->assertSee($document->document_type->label())->assertSee($version->slot->label())
            ->assertSee('Secure preview')->assertSee('Secure download')->assertDontSee('Upload');
        $this->actingAs($japic)->get(route('japic.fea.documents.versions.preview', [$fea, $document, $version]))->assertOk();
        $this->actingAs($japic)->get(route('japic.fea.documents.versions.download', [$fea, $document, $version]))->assertOk();
        $otherDocument = $fea->documents->skip(1)->first();
        $this->actingAs($japic)->get(route('japic.fea.documents.versions.preview', [$fea, $otherDocument, $version]))->assertNotFound();

        $this->actingAs($japic)->post(route('ib39.fea.documents.versions.store', [$fea, $document]))->assertForbidden();
    }

    public function test_record_pages_use_exact_empty_states_and_never_infer_assistance(): void
    {
        [$japic, $record, $processing] = $this->context(false);
        $record->cdrProcessing->update(['current_final_version_id' => null]);

        $this->actingAs($japic)->get(route('japic.certifications.records.cdr', $processing))
            ->assertOk()->assertSee('No CDR document is currently available for preview.');
        $this->actingAs($japic)->get(route('japic.certifications.records.fea', $processing))
            ->assertOk()->assertSee('No FEA processing documents are currently available for preview.');
        $this->actingAs($japic)->get(route('japic.certifications.records.assistance', $processing))
            ->assertOk()->assertSee('No assistance records are currently available.');
        $this->assertSame(0, DB::table('fr_government_assistances')->count());
    }

    private function context(bool $firearms): array
    {
        $actor = User::factory()->role('39th_ib')->create();
        $japic = User::factory()->role('japic')->create();
        $municipality = DB::table('municipalities')->insertGetId(['name' => 'Access '.uniqid(), 'created_at' => now(), 'updated_at' => now()]);
        $record = app(Ib39SurfacedFormerRebelService::class)->create(['first_name' => 'Secure', 'last_name' => 'Access',
            'category' => Ib39FrCategory::RegularMember->value, 'province' => Ib39SurfacedFormerRebel::DEFAULT_PROVINCE,
            'municipality_id' => $municipality, 'surfaced_at' => '2026-09-01', 'possessed_firearms' => $firearms], $actor)->load('cdrProcessing', 'feaProcessing.documents');
        $cdr = $record->cdrProcessing;
        $versionId = DB::table('ib39_cdr_document_versions')->insertGetId(['cdr_processing_id' => $cdr->id, 'version_number' => 1,
            'source_type' => 'uploaded', 'storage_path' => "ib39/cdr/{$cdr->id}/final.pdf", 'original_filename' => 'final.pdf', 'mime_type' => 'application/pdf',
            'size_bytes' => 1, 'sha256' => str_repeat('c', 64), 'created_by' => $actor->id, 'finalized_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $cdr->update(['status' => 'Completed', 'completed_at' => now(), 'completed_by' => $actor->id, 'current_final_version_id' => $versionId]);
        $processing = JapicCertificationProcessing::query()->forceCreate(['ib39_surfaced_former_rebel_id' => $record->id,
            'triggering_cdr_document_version_id' => $versionId, 'status' => JapicCertificationStatus::Pending,
            'received_at' => now(), 'due_at' => now()->addDays(14), 'lock_version' => 0]);

        return [$japic, $record, $processing, $cdr->documentVersions()->findOrFail($versionId)];
    }
}
