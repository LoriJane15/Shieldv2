<?php

namespace Tests\Feature;

use App\Enums\Ib39FeaDocumentStatus;
use App\Enums\Ib39FeaDocumentType;
use App\Enums\Ib39FeaUploadSlot;
use App\Enums\Ib39FrCategory;
use App\Models\Barangay;
use App\Models\Ib39FeaDocument;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\Municipality;
use App\Models\User;
use App\Services\Ib39FeaDraftSchema;
use App\Services\Ib39SurfacedFormerRebelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Ib39FeaDraftUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private Ib39SurfacedFormerRebel $record;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->actor = User::factory()->role('39th_ib')->create();
        $municipality = Municipality::query()->create(['name' => 'Upload Test Municipality']);
        $barangay = Barangay::query()->create(['municipality_id' => $municipality->id, 'name' => 'Upload Test Barangay']);
        $this->record = app(Ib39SurfacedFormerRebelService::class)->create([
            'first_name' => 'Synthetic', 'last_name' => 'Upload Subject', 'category' => Ib39FrCategory::RegularMember->value,
            'municipality_id' => $municipality->id, 'barangay_id' => $barangay->id,
            'surfaced_at' => '2026-09-01', 'possessed_firearms' => true,
        ], $this->actor);
    }

    public function test_all_four_document_requirements_accept_pdf_and_first_upload_starts_processing(): void
    {
        foreach ([Ib39FeaDocumentType::Tir, Ib39FeaDocumentType::Cvif, Ib39FeaDocumentType::Ptis, Ib39FeaDocumentType::Justification] as $type) {
            $document = $this->document($type);
            $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf($type->value.'.pdf')])->assertRedirect();
            $this->assertSame('application/pdf', $document->fresh()->currentDraftVersion->mime_type);
            $this->assertSame(Ib39FeaDocumentStatus::Processing, $document->fresh()->status);
            $this->assertNull($document->fresh()->completed_at);
        }
        $this->assertDatabaseCount('ib39_fea_document_versions', 4);
        $this->assertDatabaseCount('ib39_fea_upload_histories', 4);
    }

    public function test_both_photo_requirements_accept_decoded_jpeg_and_png(): void
    {
        $this->requireGd();
        $files = [
            Ib39FeaDocumentType::FirearmPhoto->value => UploadedFile::fake()->image('firearm.jpg', 40, 30),
            Ib39FeaDocumentType::FrWithFirearmPhoto->value => UploadedFile::fake()->image('fr-with-firearm.png', 40, 30),
        ];
        foreach ([Ib39FeaDocumentType::FirearmPhoto, Ib39FeaDocumentType::FrWithFirearmPhoto] as $type) {
            $document = $this->document($type);
            $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $files[$type->value]])->assertRedirect();
        }
        $firearm = $this->document(Ib39FeaDocumentType::FirearmPhoto);
        $first = $firearm->fresh()->currentDraftVersion;
        $this->assertSame('image/jpeg', $first->mime_type);
        $this->assertSame('image/png', $this->document(Ib39FeaDocumentType::FrWithFirearmPhoto)->fresh()->currentDraftVersion->mime_type);
        $this->actingAs($this->actor)->post($this->storeUrl($firearm), [
            'file' => UploadedFile::fake()->image('corrected-firearm.png', 50, 35),
            'expected_current_version_id' => $first->id,
            'replacement_reason' => 'Corrected synthetic photograph',
        ])->assertRedirect();
        $replacement = $firearm->fresh()->currentDraftVersion;
        $this->assertSame(2, $replacement->version_number);
        $this->assertSame($first->id, $replacement->replaces_version_id);
        $this->assertSame('image/png', $replacement->mime_type);
        $this->actingAs($this->actor)->get(route('ib39.fea.documents.versions.preview', [$firearm->processing, $firearm, $replacement]))
            ->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertNotSame(Ib39FeaDocumentStatus::Completed, $firearm->fresh()->status);
        $this->assertNull($firearm->fresh()->completed_at);
        $this->assertSame(6, $this->record->feaProcessing->documents()->count());
        $this->assertDatabaseCount('ib39_fea_document_versions', 3);
        $this->assertDatabaseCount('ib39_fea_upload_histories', 3);
    }

    public function test_spoofed_malformed_truncated_unsupported_and_oversized_files_are_rejected(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Tir);
        foreach ([
            UploadedFile::fake()->createWithContent('draft.docx', 'PK synthetic'),
            UploadedFile::fake()->createWithContent('draft.svg', '<svg/>'),
            UploadedFile::fake()->createWithContent('draft.html', '<html/>'),
            UploadedFile::fake()->createWithContent('draft.exe', "MZ\0"),
            UploadedFile::fake()->createWithContent('spoof.pdf', '<html/>'),
            UploadedFile::fake()->createWithContent('truncated.pdf', '%PDF-1.7 missing eof'),
        ] as $file) {
            $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $file])->assertSessionHasErrors('file');
        }
        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => UploadedFile::fake()->create('large.pdf', 20481, 'application/pdf')])->assertSessionHasErrors('file');
        $photo = $this->document(Ib39FeaDocumentType::FirearmPhoto);
        $this->actingAs($this->actor)->post($this->storeUrl($photo), ['file' => UploadedFile::fake()->createWithContent('bad.png', 'not an image')])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('ib39_fea_document_versions', 0);
    }

    public function test_photo_validation_errors_remain_visible_in_the_workspace_and_justification_editor(): void
    {
        $photo = $this->document(Ib39FeaDocumentType::FirearmPhoto);
        $workspaceUrl = route('ib39.fea.show', $photo->processing);
        $this->actingAs($this->actor)->from($workspaceUrl)->post($this->storeUrl($photo), [
            'file' => UploadedFile::fake()->createWithContent('invalid.png', 'not an image'),
        ])->assertRedirect($workspaceUrl)->assertSessionHasErrors('file');
        $this->actingAs($this->actor)->get($workspaceUrl)->assertOk()->assertSee('validation-summary', false);

        $justification = $this->document(Ib39FeaDocumentType::Justification);
        $editorUrl = route('ib39.fea.documents.draft.edit', [$justification->processing, $justification]);
        $this->actingAs($this->actor)->from($editorUrl)->post(route('ib39.fea.documents.surrendered-versions.store', [$justification->processing, $justification]), [
            'file' => UploadedFile::fake()->createWithContent('invalid.png', 'not an image'),
        ])->assertRedirect($editorUrl)->assertSessionHasErrors('file');
        $this->actingAs($this->actor)->get($editorUrl)->assertOk()->assertSee('alert alert-danger', false);
    }

    public function test_replacement_is_immutable_requires_reason_rejects_stale_and_deduplicates_retry(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Tir);
        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf()])->assertRedirect();
        $first = $document->fresh()->currentDraftVersion;
        $firstPath = $first->getRawOriginal('storage_path');

        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf('same.pdf'), 'expected_current_version_id' => $first->id, 'replacement_reason' => 'Retry'])->assertRedirect();
        $this->assertDatabaseCount('ib39_fea_document_versions', 1);
        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf('changed.pdf', 'changed'), 'expected_current_version_id' => $first->id])->assertSessionHasErrors('replacement_reason');
        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf('changed.pdf', 'changed'), 'expected_current_version_id' => $first->id, 'replacement_reason' => 'Corrected layout'])->assertRedirect();
        $second = $document->fresh()->currentDraftVersion;
        $this->assertSame(2, $second->version_number);
        $this->assertSame($first->id, $second->replaces_version_id);
        $this->assertSame('Corrected layout', $second->replacement_reason);
        $this->assertStringNotContainsString('Corrected layout', (string) DB::table('ib39_fea_document_versions')->where('id', $second->id)->value('replacement_reason'));
        Storage::disk('local')->assertExists($firstPath);

        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf('stale.pdf', 'stale'), 'expected_current_version_id' => $first->id, 'replacement_reason' => 'Stale'])->assertSessionHasErrors('expected_current_version_id');
        $this->assertSame($second->id, $document->fresh()->current_draft_version_id);
    }

    public function test_private_access_enforces_role_and_exact_processing_document_version_ownership(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Tir);
        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf()]);
        $version = $document->fresh()->currentDraftVersion;
        $preview = route('ib39.fea.documents.versions.preview', [$document->processing, $document, $version]);
        $response = $this->actingAs($this->actor)->get($preview)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->actingAs(User::factory()->role('admin')->create())->get($preview)->assertForbidden();
        $other = $this->otherRecord()->feaProcessing;
        $this->actingAs($this->actor)->get(route('ib39.fea.documents.versions.preview', [$other, $document, $version]))->assertForbidden();
        $otherDocument = $other->documents()->where('document_type', Ib39FeaDocumentType::Tir)->firstOrFail();
        $this->actingAs($this->actor)->get(route('ib39.fea.documents.versions.preview', [$other, $otherDocument, $version]))->assertNotFound();
        Storage::disk('local')->delete($version->getRawOriginal('storage_path'));
        $this->actingAs($this->actor)->get($preview)->assertNotFound();
    }

    public function test_structured_draft_and_metadata_are_preserved_and_histories_never_contain_path_hash_or_reason(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Cvif);
        $draft = app(Ib39FeaDraftSchema::class)->initial($document->document_type, 'Saved Name');
        $document->update(['draft_data' => $draft, 'draft_schema_version' => 1, 'draft_revision' => 4, 'remarks' => 'Existing remarks', 'is_delayed' => true, 'delay_reason' => 'Existing delay']);
        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf()])->assertRedirect();
        $saved = $document->fresh();
        $this->assertSame($draft, $saved->draft_data);
        $this->assertSame(4, $saved->draft_revision);
        $this->assertSame('Existing remarks', $saved->remarks);
        $this->assertSame('Existing delay', $saved->delay_reason);
        $version = $saved->currentDraftVersion;
        $history = DB::table('ib39_fea_upload_histories')->where('fea_document_version_id', $version->id)->first();
        $audit = DB::table('audit_logs')->where('entity_id', $version->id)->latest('id')->first();
        $safe = json_encode([$history, $audit]);
        $this->assertStringNotContainsString($version->getRawOriginal('storage_path'), $safe);
        $this->assertStringNotContainsString($version->sha256, $safe);
    }

    public function test_failed_replacement_rolls_back_pointer_history_and_status_and_removes_only_staged_file(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Tir);
        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf()])->assertRedirect();
        $first = $document->fresh()->currentDraftVersion;
        $firstPath = $first->getRawOriginal('storage_path');
        DB::unprepared("CREATE TRIGGER fail_fea_upload_audit BEFORE INSERT ON audit_logs WHEN NEW.action = 'ib39_fea_draft_file_replaced' BEGIN SELECT RAISE(ABORT, 'simulated FEA failure'); END");
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($this->actor)->post($this->storeUrl($document), [
                'file' => $this->pdf('replacement.pdf', 'different'),
                'expected_current_version_id' => $first->id,
                'replacement_reason' => 'Must roll back',
            ]);
            $this->fail('The simulated database failure did not abort the replacement.');
        } catch (\Throwable $exception) {
            $this->assertStringContainsString('simulated FEA failure', $exception->getMessage());
        }

        $this->assertSame($first->id, $document->fresh()->current_draft_version_id);
        $this->assertDatabaseCount('ib39_fea_document_versions', 1);
        $this->assertDatabaseCount('ib39_fea_upload_histories', 1);
        Storage::disk('local')->assertExists($firstPath);
        $this->assertCount(1, Storage::disk('local')->allFiles("ib39/fea/{$document->fea_processing_id}/drafts/primary"));
    }

    public function test_all_four_photo_streams_are_independent_and_replacing_one_changes_no_other_pointer(): void
    {
        $this->requireGd();
        $firearm = $this->document(Ib39FeaDocumentType::FirearmPhoto);
        $frWithFirearm = $this->document(Ib39FeaDocumentType::FrWithFirearmPhoto);
        $justification = $this->document(Ib39FeaDocumentType::Justification);
        $this->actingAs($this->actor)->post($this->storeUrl($firearm), ['file' => UploadedFile::fake()->image('firearm.jpg', 40, 30)])->assertRedirect();
        $this->actingAs($this->actor)->post($this->storeUrl($frWithFirearm), ['file' => UploadedFile::fake()->image('fr-firearm.jpg', 40, 30)])->assertRedirect();
        $this->actingAs($this->actor)->post(route('ib39.fea.documents.surrendered-versions.store', [$justification->processing, $justification]), ['file' => UploadedFile::fake()->image('surrendered.png', 40, 30)])->assertRedirect();
        $this->actingAs($this->actor)->post(route('ib39.fea.documents.comparison-versions.store', [$justification->processing, $justification]), ['file' => UploadedFile::fake()->image('comparison.png', 40, 30)])->assertRedirect();
        $justification->update(['draft_data' => app(Ib39FeaDraftSchema::class)->initial(Ib39FeaDocumentType::Justification, ''), 'draft_schema_version' => Ib39FeaDraftSchema::VERSION, 'draft_revision' => 1]);

        $pointers = [
            $firearm->fresh()->current_draft_version_id,
            $frWithFirearm->fresh()->current_draft_version_id,
            $justification->fresh()->current_surrendered_photo_version_id,
            $justification->fresh()->current_supporting_photo_version_id,
        ];
        $sectionThree = $justification->fresh()->currentSurrenderedPhotoVersion;
        $this->actingAs($this->actor)->post(route('ib39.fea.documents.surrendered-versions.store', [$justification->processing, $justification]), [
            'file' => UploadedFile::fake()->image('surrendered-replacement.png', 45, 35),
            'expected_current_version_id' => $sectionThree->id,
            'replacement_reason' => 'Clearer Section 3 image',
        ])->assertRedirect();

        $this->assertSame($pointers[0], $firearm->fresh()->current_draft_version_id);
        $this->assertSame($pointers[1], $frWithFirearm->fresh()->current_draft_version_id);
        $this->assertNotSame($pointers[2], $justification->fresh()->current_surrendered_photo_version_id);
        $this->assertSame($pointers[3], $justification->fresh()->current_supporting_photo_version_id);
        $response = $this->actingAs($this->actor)->get(route('ib39.fea.documents.draft.preview', [$justification->processing, $justification]))->assertOk();
        $response->assertDontSee(route('ib39.fea.documents.versions.preview', [$firearm->processing, $firearm, $firearm->fresh()->currentDraftVersion]), false);
        $response->assertDontSee(route('ib39.fea.documents.versions.preview', [$frWithFirearm->processing, $frWithFirearm, $frWithFirearm->fresh()->currentDraftVersion]), false);
        $response->assertSee(route('ib39.fea.documents.versions.preview', [$justification->processing, $justification, $justification->fresh()->currentSurrenderedPhotoVersion]), false);
        $response->assertSee(route('ib39.fea.documents.versions.preview', [$justification->processing, $justification, $justification->fresh()->currentSupportingPhotoVersion]), false);
        $this->assertSame(6, $this->record->feaProcessing->documents()->count());
        $this->assertSame(Ib39FeaUploadSlot::JustificationSurrendered, $justification->fresh()->currentSurrenderedPhotoVersion->slot);
        $this->assertSame(Ib39FeaUploadSlot::JustificationComparison, $justification->fresh()->currentSupportingPhotoVersion->slot);
        $this->assertSame(2, $justification->versions()->where('slot', Ib39FeaUploadSlot::JustificationSurrendered)->count());
    }

    public function test_completed_ineligible_deleted_and_server_owned_final_fields_are_rejected(): void
    {
        $document = $this->document(Ib39FeaDocumentType::Ptis);
        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf(), 'status' => 'Completed', 'final_copy' => true, 'pswdo_id' => 1])->assertSessionHasErrors(['status', 'final_copy', 'pswdo_id']);
        $document->update(['status' => Ib39FeaDocumentStatus::Completed]);
        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf()])->assertForbidden();
        $document->update(['status' => Ib39FeaDocumentStatus::Pending]);
        $this->record->delete();
        $this->actingAs($this->actor)->post($this->storeUrl($document), ['file' => $this->pdf()])->assertForbidden();
    }

    public function test_section_three_migration_rollback_refuses_without_changing_data_or_schema(): void
    {
        $this->requireGd();
        $document = $this->document(Ib39FeaDocumentType::Justification);
        $this->actingAs($this->actor)->post(route('ib39.fea.documents.surrendered-versions.store', [$document->processing, $document]), [
            'file' => UploadedFile::fake()->image('section-three.png', 40, 30),
        ])->assertRedirect();

        $versionId = $document->fresh()->current_surrendered_photo_version_id;
        $columns = Schema::getColumnListing('ib39_fea_documents');
        $migration = require database_path('migrations/2026_09_02_000002_add_surrendered_photo_stream_to_ib39_fea_documents.php');

        try {
            $migration->down();
            $this->fail('Rollback did not refuse an existing Section 3 version.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Cannot roll back while Justification Section 3 photo versions exist.', $exception->getMessage());
        }

        $this->assertSame($columns, Schema::getColumnListing('ib39_fea_documents'));
        $this->assertSame($versionId, $document->fresh()->current_surrendered_photo_version_id);
        $this->assertDatabaseHas('ib39_fea_document_versions', ['id' => $versionId, 'slot' => Ib39FeaUploadSlot::JustificationSurrendered->value]);
        $this->assertDatabaseHas('ib39_fea_upload_histories', ['fea_document_version_id' => $versionId, 'slot' => Ib39FeaUploadSlot::JustificationSurrendered->value]);
    }

    private function document(Ib39FeaDocumentType $type): Ib39FeaDocument
    {
        return $this->record->feaProcessing->documents()->where('document_type', $type)->firstOrFail();
    }

    private function storeUrl(Ib39FeaDocument $document): string
    {
        return route('ib39.fea.documents.versions.store', [$document->processing, $document]);
    }

    private function pdf(string $name = 'prepared-draft.pdf', string $body = 'synthetic'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n{$body}\n%%EOF");
    }

    private function otherRecord(): Ib39SurfacedFormerRebel
    {
        return app(Ib39SurfacedFormerRebelService::class)->create([
            'first_name' => 'Other', 'last_name' => 'Upload', 'category' => Ib39FrCategory::RegularMember->value,
            'municipality_id' => $this->record->municipality_id, 'barangay_id' => $this->record->barangay_id,
            'surfaced_at' => '2026-09-01', 'possessed_firearms' => true,
        ], $this->actor);
    }

    private function requireGd(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD is required to generate and decode synthetic JPEG/PNG fixtures.');
        }
    }
}
