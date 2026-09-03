<?php

namespace Tests\Feature;

use App\Enums\Ib39FeaComplianceStatus;
use App\Enums\Ib39FeaDocumentHistoryEvent;
use App\Enums\Ib39FeaDocumentStatus;
use App\Enums\Ib39FeaOverallStatus;
use App\Enums\Ib39FrCategory;
use App\Models\AuditLog;
use App\Models\Barangay;
use App\Models\Ib39FeaDocumentHistory;
use App\Models\Municipality;
use App\Models\User;
use App\Services\Ib39SurfacedFormerRebelService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class Ib39FeaPreliminaryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_document_starts_once_with_server_owned_metadata_and_safe_audit(): void
    {
        [$actor, $processing, $document] = $this->context();
        $url = route('ib39.fea.documents.start', [$processing, $document]);

        $this->actingAs($actor)->post($url)->assertRedirect(route('ib39.fea.show', $processing));
        $started = $document->fresh();
        $this->assertSame(Ib39FeaDocumentStatus::Processing, $started->status);
        $this->assertNotNull($started->started_at);
        $this->assertSame($actor->id, $started->prepared_by);
        $this->assertSame($actor->id, $started->last_updated_by);
        $this->assertDatabaseCount('ib39_fea_document_histories', 1);
        $this->assertSame(Ib39FeaDocumentHistoryEvent::ProcessingStarted, $started->histories()->sole()->event);

        $audit = AuditLog::query()->where('action', 'ib39_fea_preliminary_work_started')->sole();
        $this->assertSame(['document_type', 'changed_fields'], array_keys($audit->new_values));
        $this->assertNull($audit->ip_address);
        $this->assertNull($audit->user_agent);

        $this->actingAs($actor)->post($url)->assertRedirect();
        $this->assertDatabaseCount('ib39_fea_document_histories', 1);
        $this->assertSame(1, AuditLog::query()->where('action', 'ib39_fea_preliminary_work_started')->count());
    }

    public function test_update_records_only_meaningful_changes_and_keeps_overall_awaiting_pswdo(): void
    {
        [$actor, $processing, $document] = $this->context();
        $this->actingAs($actor)->post(route('ib39.fea.documents.start', [$processing, $document]));

        $payload = $this->payload([
            'remarks' => '  Preliminary inspection notes.  ',
            'compliance_status' => Ib39FeaComplianceStatus::HasIssue->value,
            'compliance_reason' => '  Component requires review.  ',
            'is_delayed' => '1',
            'delay_reason' => '  Awaiting authorized specialist.  ',
        ]);
        $url = route('ib39.fea.documents.update', [$processing, $document]);
        $this->actingAs($actor)->patch($url, $payload)->assertRedirect();

        $updated = $document->fresh();
        $this->assertSame('Preliminary inspection notes.', $updated->remarks);
        $this->assertSame(Ib39FeaComplianceStatus::HasIssue, $updated->compliance_status);
        $this->assertSame('Component requires review.', $updated->compliance_reason);
        $this->assertTrue($updated->is_delayed);
        $this->assertSame('Awaiting authorized specialist.', $updated->delay_reason);
        $this->assertSame($actor->id, $updated->last_updated_by);
        $this->assertDatabaseCount('ib39_fea_document_histories', 4);
        $this->assertDatabaseCount('ib39_fea_processing_histories', 0);
        $this->assertSame(Ib39FeaOverallStatus::AwaitingPswdoEnrollment, $processing->fresh()->overallStatus());

        $this->actingAs($actor)->patch($url, $this->payload([
            'remarks' => 'Preliminary inspection notes.',
            'compliance_status' => Ib39FeaComplianceStatus::HasIssue->value,
            'compliance_reason' => 'Component requires review.',
            'is_delayed' => true,
            'delay_reason' => 'Awaiting authorized specialist.',
        ]))->assertRedirect();
        $this->assertDatabaseCount('ib39_fea_document_histories', 4);
        $this->assertSame(1, AuditLog::query()->where('action', 'ib39_fea_preliminary_document_updated')->count());
    }

    public function test_compliance_and_delay_reasons_are_required_and_obsolete_values_are_cleared(): void
    {
        [$actor, $processing, $document] = $this->startedContext();
        $url = route('ib39.fea.documents.update', [$processing, $document]);

        $this->actingAs($actor)->patch($url, $this->payload([
            'compliance_status' => Ib39FeaComplianceStatus::ReturnedForCompliance->value,
            'compliance_reason' => '',
        ]))->assertSessionHasErrors('document.compliance_reason');
        $this->actingAs($actor)->patch($url, $this->payload([
            'is_delayed' => true,
            'delay_reason' => '',
        ]))->assertSessionHasErrors('document.delay_reason');
        $this->assertDatabaseCount('ib39_fea_document_histories', 1);

        $this->actingAs($actor)->patch($url, $this->payload([
            'compliance_status' => Ib39FeaComplianceStatus::HasIssue->value,
            'compliance_reason' => 'Needs review',
            'is_delayed' => true,
            'delay_reason' => 'Awaiting review',
        ]))->assertSessionHasNoErrors();
        $this->actingAs($actor)->patch($url, $this->payload([
            'compliance_status' => Ib39FeaComplianceStatus::None->value,
            'compliance_reason' => 'Must be cleared',
            'is_delayed' => false,
            'delay_reason' => 'Must be cleared',
        ]))->assertSessionHasNoErrors();

        $cleared = $document->fresh();
        $this->assertNull($cleared->compliance_reason);
        $this->assertFalse($cleared->is_delayed);
        $this->assertNull($cleared->delay_reason);
    }

    public function test_completed_pending_and_server_owned_status_inputs_are_rejected_without_writes(): void
    {
        [$actor, $processing, $document] = $this->startedContext();
        $url = route('ib39.fea.documents.update', [$processing, $document]);

        foreach ([Ib39FeaDocumentStatus::Completed->value, Ib39FeaDocumentStatus::Pending->value] as $status) {
            $this->actingAs($actor)->patch($url, $this->payload(['status' => $status]))
                ->assertSessionHasErrors('document.status');
        }

        $this->actingAs($actor)->patch($url, [
            ...$this->payload(),
            'overall_status' => 'Completed',
            'pswdo_status' => 'Completed',
        ])->assertSessionHasErrors('request');

        $this->actingAs($actor)->patch($url, ['document' => [
            ...$this->payload()['document'],
            'is_required' => false,
            'prepared_by' => $actor->id,
        ]])->assertSessionHasErrors('document');
        $this->assertDatabaseCount('ib39_fea_document_histories', 1);
        $this->assertSame(Ib39FeaDocumentStatus::Processing, $document->fresh()->status);
    }

    public function test_arrays_are_not_cast_to_strings_and_bounded_text_is_enforced(): void
    {
        [$actor, $processing, $document] = $this->startedContext();
        $url = route('ib39.fea.documents.update', [$processing, $document]);

        foreach (['remarks', 'compliance_reason', 'delay_reason'] as $field) {
            $this->actingAs($actor)->patch($url, $this->payload([$field => ['unsafe']]))
                ->assertSessionHasErrors("document.{$field}");
        }
        $this->actingAs($actor)->patch($url, $this->payload(['remarks' => str_repeat('x', 2001)]))
            ->assertSessionHasErrors('document.remarks');
        $this->assertDatabaseCount('ib39_fea_document_histories', 1);
    }

    public function test_document_update_and_history_are_atomic(): void
    {
        [$actor, $processing, $document] = $this->startedContext();
        DB::unprepared("CREATE TRIGGER fail_fea_history BEFORE INSERT ON ib39_fea_document_histories BEGIN SELECT RAISE(ABORT, 'history failure'); END");
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($actor)->patch(route('ib39.fea.documents.update', [$processing, $document]), $this->payload(['remarks' => 'Must roll back']));
            $this->fail('History failure did not abort the update.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('history failure', $exception->getMessage());
        }

        $this->assertNull($document->fresh()->remarks);
        $this->assertDatabaseCount('ib39_fea_document_histories', 1);
        $this->assertSame(0, AuditLog::query()->where('action', 'ib39_fea_preliminary_document_updated')->count());
    }

    public function test_history_is_immutable_and_sensitive_text_is_encrypted_and_absent_from_audit(): void
    {
        [$actor, $processing, $document] = $this->startedContext();
        $secret = 'Synthetic confidential preliminary note';
        $this->actingAs($actor)->patch(route('ib39.fea.documents.update', [$processing, $document]), $this->payload(['remarks' => $secret]));

        $history = Ib39FeaDocumentHistory::query()->where('event', Ib39FeaDocumentHistoryEvent::RemarksChanged)->sole();
        $this->assertStringNotContainsString($secret, DB::table('ib39_fea_document_histories')->where('id', $history->id)->value('new_values'));
        $this->assertStringNotContainsString($secret, json_encode(AuditLog::query()->where('action', 'ib39_fea_preliminary_document_updated')->sole()->new_values));

        try {
            $history->update(['event' => Ib39FeaDocumentHistoryEvent::DelayChanged]);
            $this->fail('Immutable history was updated.');
        } catch (LogicException) {
            $this->assertTrue(true);
        }
    }

    private function context(): array
    {
        $actor = User::factory()->role('39th_ib')->create(['name' => 'Safe Stage Two Actor']);
        $municipality = Municipality::query()->create(['name' => fake()->unique()->city()]);
        $barangay = Barangay::query()->create(['municipality_id' => $municipality->id, 'name' => fake()->unique()->streetName()]);
        $record = app(Ib39SurfacedFormerRebelService::class)->create([
            'first_name' => 'Synthetic', 'last_name' => 'Workflow',
            'category' => Ib39FrCategory::RegularMember->value,
            'municipality_id' => $municipality->id, 'barangay_id' => $barangay->id,
            'surfaced_at' => '2026-08-30', 'possessed_firearms' => true,
        ], $actor);
        $processing = $record->feaProcessing()->firstOrFail();

        return [$actor, $processing, $processing->documents()->firstOrFail()];
    }

    private function startedContext(): array
    {
        [$actor, $processing, $document] = $this->context();
        $this->actingAs($actor)->post(route('ib39.fea.documents.start', [$processing, $document]));

        return [$actor, $processing, $document->fresh()];
    }

    private function payload(array $overrides = []): array
    {
        return ['document' => [
            'status' => Ib39FeaDocumentStatus::Processing->value,
            'compliance_status' => Ib39FeaComplianceStatus::None->value,
            'remarks' => null,
            'compliance_reason' => null,
            'is_delayed' => false,
            'delay_reason' => null,
            ...$overrides,
        ]];
    }
}
