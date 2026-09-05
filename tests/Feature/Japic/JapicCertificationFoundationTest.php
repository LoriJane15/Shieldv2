<?php

namespace Tests\Feature\Japic;

use App\Enums\Ib39CdrDocumentSource;
use App\Enums\Ib39FrCategory;
use App\Enums\JapicCertificationDocumentSource;
use App\Enums\JapicCertificationHistoryEvent;
use App\Enums\JapicCertificationStatus;
use App\Enums\JapicCertificationTriggerSource;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\JapicCertificationDocumentVersion;
use App\Models\JapicCertificationProcessing;
use App\Models\Municipality;
use App\Models\User;
use App\Services\Ib39SurfacedFormerRebelService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class JapicCertificationFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_foundation_tables_columns_indexes_and_foreign_keys_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('ib39_fr_cancellations', [
            'ib39_surfaced_former_rebel_id', 'previous_overall_status', 'reason', 'cancelled_by', 'cancelled_at',
        ]));
        $this->assertTrue(Schema::hasColumns('japic_certification_processings', [
            'ib39_surfaced_former_rebel_id', 'triggering_cdr_document_version_id', 'status',
            'received_on', 'due_on', 'trigger_source', 'control_number', 'control_number_hash',
            'delay_reason', 'current_final_version_id',
        ]));
        $this->assertTrue(Schema::hasColumns('japic_certification_drafts', [
            'japic_certification_processing_id', 'content', 'schema_version', 'revision',
            'fr_photo_version_id', 'last_saved_by', 'saved_at',
        ]));
        $this->assertTrue(Schema::hasColumns('japic_certification_histories', [
            'japic_certification_processing_id', 'user_id', 'from_status', 'to_status',
            'event', 'remarks', 'delay_reason', 'japic_certification_document_version_id',
        ]));
        $this->assertTrue(Schema::hasColumns('japic_certification_document_versions', [
            'japic_certification_processing_id', 'version_number', 'source', 'replaces_version_id',
            'replacement_reason', 'storage_path', 'original_filename', 'mime_type', 'size_bytes',
            'sha256', 'uploaded_by', 'confirmed_correct_fr', 'confirmed_complete',
            'confirmed_signatures_present', 'confirmed_final_copy', 'confirmed_at', 'uploaded_at',
        ]));
        $processingIndexes = collect(Schema::getIndexes('japic_certification_processings'));
        $this->assertTrue($processingIndexes->contains(fn (array $index) => $index['unique']
            && $index['columns'] === ['ib39_surfaced_former_rebel_id']));
        $this->assertTrue($processingIndexes->contains(fn (array $index) => $index['unique']
            && $index['columns'] === ['control_number_hash']));
    }

    public function test_sensitive_foundation_values_are_encrypted_and_server_owned_fields_are_guarded(): void
    {
        [$processing] = $this->processing('Encrypted');
        $processing->forceFill([
            'control_number' => 'JAPIC-CONTROL-001',
            'control_number_hash' => hash('sha256', 'JAPIC-CONTROL-001'),
            'delay_reason' => 'Confidential delay reason',
        ])->save();
        $draft = $processing->draft()->forceCreate([
            'content' => ['residence' => 'Confidential test residence'],
            'schema_version' => 1,
            'revision' => 1,
        ]);
        $history = $processing->histories()->forceCreate([
            'from_status' => JapicCertificationStatus::Pending,
            'to_status' => JapicCertificationStatus::Drafting,
            'event' => JapicCertificationHistoryEvent::ProcessingStarted,
            'remarks' => 'Confidential history remark',
            'delay_reason' => 'Confidential historical delay',
        ]);

        $rawProcessing = DB::table('japic_certification_processings')->find($processing->id);
        $rawDraft = DB::table('japic_certification_drafts')->find($draft->id);
        $rawHistory = DB::table('japic_certification_histories')->find($history->id);
        $this->assertNotSame('JAPIC-CONTROL-001', $rawProcessing->control_number);
        $this->assertNotSame('Confidential delay reason', $rawProcessing->delay_reason);
        $this->assertStringNotContainsString('Confidential test residence', $rawDraft->content);
        $this->assertNotSame('Confidential history remark', $rawHistory->remarks);
        $this->assertNotSame('Confidential historical delay', $rawHistory->delay_reason);
        $this->assertSame('JAPIC-CONTROL-001', $processing->refresh()->control_number);
        $this->assertSame('Confidential test residence', $draft->refresh()->content['residence']);

        $model = new JapicCertificationProcessing;
        foreach (['status', 'received_on', 'due_on', 'trigger_source', 'current_final_version_id', 'control_number_hash'] as $field) {
            $this->assertTrue($model->isGuarded($field), $field);
        }
    }

    public function test_unique_constraints_prevent_duplicate_processing_records_and_control_numbers(): void
    {
        [$processing, $record, $actor, $cdrVersion] = $this->processing('Unique');

        $this->expectQueryException(function () use ($record, $cdrVersion): void {
            JapicCertificationProcessing::query()->forceCreate([
                'ib39_surfaced_former_rebel_id' => $record->id,
                'triggering_cdr_document_version_id' => $cdrVersion->id,
                'status' => JapicCertificationStatus::Pending,
                'received_on' => '2026-09-01',
                'due_on' => '2026-09-15',
                'trigger_source' => JapicCertificationTriggerSource::Reconciliation,
            ]);
        });

        $hash = hash('sha256', 'NORMALIZED-CONTROL');
        $processing->forceFill(['control_number' => 'NORMALIZED-CONTROL', 'control_number_hash' => $hash])->save();
        [$other] = $this->processing('Other');
        $this->expectQueryException(fn () => $other->forceFill([
            'control_number' => 'NORMALIZED-CONTROL',
            'control_number_hash' => $hash,
        ])->save());
    }

    public function test_database_guards_reject_cross_processing_current_replacement_and_history_versions(): void
    {
        [$first, , $actor] = $this->processing('First');
        [$second, , , $secondCdrVersion] = $this->processing('Second');
        $firstVersion = $this->documentVersion($first, $actor, 1);

        $this->expectQueryException(fn () => $first->forceFill(['due_on' => '2026-09-14'])->save());
        $first->refresh();

        $this->expectQueryException(fn () => $first->forceFill([
            'triggering_cdr_document_version_id' => $secondCdrVersion->id,
        ])->save());
        $first->refresh();

        $this->expectQueryException(fn () => $second->forceFill([
            'current_final_version_id' => $firstVersion->id,
        ])->save());

        $this->expectQueryException(fn () => $this->documentVersion($second, $actor, 2, $firstVersion->id, 'Replacement reason'));

        $this->expectQueryException(fn () => $second->histories()->forceCreate([
            'from_status' => JapicCertificationStatus::Pending,
            'to_status' => JapicCertificationStatus::Completed,
            'event' => JapicCertificationHistoryEvent::FinalUploaded,
            'japic_certification_document_version_id' => $firstVersion->id,
        ]));
    }

    public function test_cancelled_fr_cannot_receive_a_japic_processing_record(): void
    {
        $actor = User::factory()->role('39th_ib')->create();
        $record = $this->record($actor, 'Cancelled');
        $cdr = $record->cdrProcessing()->firstOrFail();
        $cdrVersion = $cdr->documentVersions()->create([
            'version_number' => 1,
            'source_type' => Ib39CdrDocumentSource::Generated,
            'storage_path' => "generated/cdr/{$cdr->id}/version/1",
            'original_filename' => 'generated-cdr-v1.html',
            'mime_type' => 'text/html',
            'size_bytes' => 10,
            'sha256' => str_repeat('d', 64),
            'content_schema_version' => 1,
            'content_snapshot' => [],
            'created_by' => $actor->id,
            'finalized_at' => now(),
        ]);
        $cdr->update([
            'status' => 'Completed',
            'completed_at' => now(),
            'completed_by' => $actor->id,
            'current_final_version_id' => $cdrVersion->id,
        ]);
        $record->cancellation()->forceCreate([
            'previous_overall_status' => Ib39SurfacedFormerRebel::OVERALL_CASE_STATUS_CDR_COMPLETED,
            'reason' => 'Approved cancellation reason',
            'cancelled_by' => $actor->id,
            'cancelled_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        JapicCertificationProcessing::query()->forceCreate([
            'ib39_surfaced_former_rebel_id' => $record->id,
            'triggering_cdr_document_version_id' => $cdrVersion->id,
            'status' => JapicCertificationStatus::Pending,
            'received_on' => '2026-09-01',
            'due_on' => '2026-09-15',
            'trigger_source' => JapicCertificationTriggerSource::CdrCompletion,
        ]);
    }

    public function test_history_document_versions_and_cancellations_are_immutable(): void
    {
        [$processing, $record, $actor] = $this->processing('Immutable');
        $history = $processing->histories()->forceCreate([
            'from_status' => null,
            'to_status' => JapicCertificationStatus::Pending,
            'event' => JapicCertificationHistoryEvent::EnteredQueue,
        ]);
        $version = $this->documentVersion($processing, $actor, 1);
        $cancellation = $record->cancellation()->forceCreate([
            'previous_overall_status' => Ib39SurfacedFormerRebel::OVERALL_CASE_STATUS,
            'reason' => 'Immutable cancellation reason',
            'cancelled_by' => $actor->id,
            'cancelled_at' => now(),
        ]);

        foreach ([$history, $version, $cancellation] as $model) {
            try {
                $model->forceFill(['updated_at' => now()->addMinute()])->save();
                $this->fail($model::class.' was mutable.');
            } catch (LogicException) {
                $this->assertTrue(true);
            }

            try {
                $model->delete();
                $this->fail($model::class.' was deletable.');
            } catch (LogicException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_stage_one_migrations_roll_back_and_reapply_without_changing_existing_ib39_records(): void
    {
        $actor = User::factory()->role('39th_ib')->create();
        $record = $this->record($actor, 'Migration');
        $before = [
            'fr' => DB::table('ib39_surfaced_former_rebels')->count(),
            'cdr' => DB::table('ib39_cdr_processings')->count(),
            'cdr_forms' => DB::table('ib39_cdr_forms')->count(),
        ];

        $japicMigration = require database_path('migrations/2026_09_04_000003_create_japic_certification_tables.php');
        $cancellationMigration = require database_path('migrations/2026_09_04_000002_create_ib39_fr_cancellations_table.php');
        $japicMigration->down();
        $cancellationMigration->down();

        foreach ($this->stageOneTables() as $table) {
            $this->assertFalse(Schema::hasTable($table), $table);
        }
        $this->assertSame($before, [
            'fr' => DB::table('ib39_surfaced_former_rebels')->count(),
            'cdr' => DB::table('ib39_cdr_processings')->count(),
            'cdr_forms' => DB::table('ib39_cdr_forms')->count(),
        ]);

        $cancellationMigration->up();
        $japicMigration->up();
        foreach ($this->stageOneTables() as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }
        $this->assertTrue(Ib39SurfacedFormerRebel::query()->whereKey($record->id)->exists());
    }

    private function processing(string $suffix): array
    {
        $actor = User::factory()->role('39th_ib')->create();
        $record = $this->record($actor, $suffix);
        $cdr = $record->cdrProcessing()->firstOrFail();
        $cdrVersion = $cdr->documentVersions()->create([
            'version_number' => 1,
            'source_type' => Ib39CdrDocumentSource::Generated,
            'storage_path' => "generated/cdr/{$cdr->id}/version/1",
            'original_filename' => 'generated-cdr-v1.html',
            'mime_type' => 'text/html',
            'size_bytes' => 10,
            'sha256' => str_repeat('b', 64),
            'content_schema_version' => 1,
            'content_snapshot' => [],
            'created_by' => $actor->id,
            'finalized_at' => now(),
        ]);
        $cdr->update([
            'status' => 'Completed',
            'completed_at' => now(),
            'completed_by' => $actor->id,
            'current_final_version_id' => $cdrVersion->id,
        ]);
        $processing = JapicCertificationProcessing::query()->forceCreate([
            'ib39_surfaced_former_rebel_id' => $record->id,
            'triggering_cdr_document_version_id' => $cdrVersion->id,
            'status' => JapicCertificationStatus::Pending,
            'received_on' => '2026-09-01',
            'due_on' => '2026-09-15',
            'trigger_source' => JapicCertificationTriggerSource::CdrCompletion,
        ]);

        return [$processing, $record, $actor, $cdrVersion];
    }

    private function record(User $actor, string $suffix): Ib39SurfacedFormerRebel
    {
        $municipality = Municipality::query()->firstOrCreate(['name' => 'JAPIC Foundation Municipality']);

        return app(Ib39SurfacedFormerRebelService::class)->create([
            'first_name' => 'Synthetic',
            'last_name' => $suffix,
            'category' => Ib39FrCategory::RegularMember->value,
            'province' => Ib39SurfacedFormerRebel::DEFAULT_PROVINCE,
            'municipality_id' => $municipality->id,
            'barangay_id' => null,
            'specific_location' => null,
            'surfaced_at' => '2026-09-01',
            'possessed_firearms' => false,
            'initial_remarks' => null,
        ], $actor);
    }

    private function documentVersion(
        JapicCertificationProcessing $processing,
        User $actor,
        int $versionNumber,
        ?int $replacesVersionId = null,
        ?string $replacementReason = null,
    ): JapicCertificationDocumentVersion {
        return $processing->documentVersions()->forceCreate([
            'version_number' => $versionNumber,
            'source' => JapicCertificationDocumentSource::ExternallyPrepared,
            'replaces_version_id' => $replacesVersionId,
            'replacement_reason' => $replacementReason,
            'storage_path' => "japic/certifications/{$processing->id}/".str_repeat('a', 32).'.pdf',
            'original_filename' => "certification-v{$versionNumber}.pdf",
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'sha256' => str_repeat('c', 64),
            'uploaded_by' => $actor->id,
            'confirmed_correct_fr' => true,
            'confirmed_complete' => true,
            'confirmed_signatures_present' => true,
            'confirmed_final_copy' => true,
            'confirmed_at' => now(),
            'uploaded_at' => now(),
        ]);
    }

    private function expectQueryException(callable $operation): void
    {
        try {
            $operation();
            $this->fail('The database constraint did not reject invalid data.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }
    }

    private function stageOneTables(): array
    {
        return [
            'ib39_fr_cancellations',
            'japic_certification_processings',
            'japic_certification_drafts',
            'japic_certification_histories',
            'japic_certification_document_versions',
        ];
    }
}
