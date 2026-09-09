<?php

namespace Tests\Feature;

use App\Enums\JapicCertificationStatus;
use App\Models\Ib39CdrDocumentVersion;
use App\Models\JapicCertificationProcessing;
use App\Models\User;
use App\Services\JapicCertificationDraftService;
use App\Services\JapicCertificationWorkflowService;
use App\Support\JapicCertificationDraftSchema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JapicCertificationPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_and_print_share_official_draft_renderer_without_private_paths_or_scanned_artifacts(): void
    {
        Storage::fake('local');
        [$processing, $japic] = $this->processingWithPhoto();
        $this->save($processing, $japic, 'First Place');
        foreach (['japic.certifications.preview', 'japic.certifications.print'] as $route) {
            $response = $this->actingAs($japic)->get(route($route, $processing))->assertOk()
                ->assertHeader('Cache-Control')->assertSee('DRAFT — NOT FINAL')->assertSee('JOINT AFP-PNP')
                ->assertSee('Official emblem pending.')->assertSee('PREPARED BY:')->assertSee('ATTESTED BY:')
                ->assertSee('Enhanced Comprehensive Local Integration Program(E-CLIP)')->assertSee('First Place')
                ->assertDontSee('CamScanner')->assertDontSee('private/japic')->assertDontSee('storage_path');
            $this->assertStringContainsString('@page{size:A4 portrait', $response->getContent());
        }
    }

    public function test_for_signing_preview_uses_the_frozen_history_revision(): void
    {
        Storage::fake('local');
        [$processing, $japic] = $this->processingWithPhoto();
        $this->save($processing, $japic, 'Frozen Place');
        app(JapicCertificationWorkflowService::class)->submitForSigning($processing->fresh(), 1, 1, null, $japic);
        $draft = $processing->draft()->firstOrFail();
        $draft->payload = ['schema_version' => 1, 'certificate' => ['surrender_location' => 'Tampered']];
        $draft->save();
        $this->actingAs($japic)->get(route('japic.certifications.preview', $processing))->assertOk()->assertSee('Frozen Place')->assertDontSee('Tampered');
    }

    private function save(JapicCertificationProcessing $processing, User $japic, string $place): void
    {
        app(JapicCertificationDraftService::class)->save($processing, ['certificate' => ['date_issued' => '2026-09-09', 'surrendering_unit' => '39IB',
            'surrender_date' => '2026-08-01', 'surrender_location' => $place], 'signatories' => collect(JapicCertificationDraftSchema::POSITIONS)
                ->map(fn () => ['rank' => 'CPT', 'name' => 'TEST OFFICER', 'suffix' => null])->all()], 'CTRL-PREVIEW', 0, 0, null, $japic);
    }

    private function processingWithPhoto(): array
    {
        $japic = User::factory()->role('japic')->create();
        $actor = User::factory()->role('39th_ib')->create();
        $municipality = DB::table('municipalities')->insertGetId(['name' => 'Preview City', 'created_at' => now(), 'updated_at' => now()]);
        $fr = DB::table('ib39_surfaced_former_rebels')->insertGetId(['reference_number' => 'FR-PREVIEW', 'first_name' => 'Preview', 'last_name' => 'Subject', 'category' => 'Regular Member',
            'province' => 'Davao del Sur', 'municipality_id' => $municipality, 'surfaced_at' => '2026-08-01', 'possessed_firearms' => 0, 'created_by' => $actor->id, 'created_at' => now(), 'updated_at' => now()]);
        $cdr = DB::table('ib39_cdr_processings')->insertGetId(['ib39_surfaced_former_rebel_id' => $fr, 'status' => 'Completed', 'completed_at' => now(), 'completed_by' => $actor->id, 'created_at' => now(), 'updated_at' => now()]);
        $photo = DB::table('ib39_cdr_photos')->insertGetId(['cdr_processing_id' => $cdr, 'photo_type' => 'fr_photo', 'created_at' => now(), 'updated_at' => now()]);
        $photoVersion = DB::table('ib39_cdr_photo_versions')->insertGetId(['cdr_photo_id' => $photo, 'version_number' => 1, 'storage_path' => 'ib39/test/photo.png', 'original_filename' => 'photo.png',
            'mime_type' => 'image/png', 'size_bytes' => 3, 'sha256' => str_repeat('c', 64), 'uploaded_by' => $actor->id, 'created_at' => now(), 'updated_at' => now()]);
        Storage::disk('local')->put('ib39/test/photo.png', 'png');
        DB::table('ib39_cdr_photos')->where('id', $photo)->update(['current_photo_version_id' => $photoVersion]);
        $version = Ib39CdrDocumentVersion::query()->forceCreate(['cdr_processing_id' => $cdr, 'version_number' => 1, 'source_type' => 'generated', 'storage_path' => 'generated/preview', 'original_filename' => 'preview.html',
            'mime_type' => 'text/html', 'size_bytes' => 1, 'sha256' => str_repeat('d', 64), 'content_schema_version' => 2, 'content_snapshot' => ['content' => ['alias' => 'Alias', 'gender' => 'Female',
                'classification' => 'NPSRL', 'present_address' => 'Address', 'latest_position' => 'Leader', 'organization_affiliation' => 'Organization', 'recruitment_date' => '1998'], 'fr_photo_version_id' => $photoVersion], 'created_by' => $actor->id, 'finalized_at' => now()]);
        DB::table('ib39_cdr_processings')->where('id', $cdr)->update(['current_final_version_id' => $version->id]);

        return [JapicCertificationProcessing::query()->forceCreate(['ib39_surfaced_former_rebel_id' => $fr, 'triggering_cdr_document_version_id' => $version->id,
            'status' => JapicCertificationStatus::Pending, 'received_at' => now(), 'due_at' => now()->addDays(14), 'lock_version' => 0]), $japic];
    }
}
