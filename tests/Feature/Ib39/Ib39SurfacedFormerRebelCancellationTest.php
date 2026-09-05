<?php

namespace Tests\Feature\Ib39;

use App\Enums\Ib39CdrDocumentSource;
use App\Enums\Ib39FrCategory;
use App\Enums\JapicCertificationStatus;
use App\Enums\JapicCertificationTriggerSource;
use App\Models\AuditLog;
use App\Models\Ib39FrCancellation;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\JapicCertificationProcessing;
use App\Models\Municipality;
use App\Models\User;
use App\Services\Ib39SurfacedFormerRebelCancellationService;
use App\Services\Ib39SurfacedFormerRebelService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Ib39SurfacedFormerRebelCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_39th_ib_user_can_cancel_from_profile_with_confirmation(): void
    {
        $actor = User::factory()->role('39th_ib')->create();
        $record = $this->record($actor);

        $this->actingAs($actor)->get(route('ib39.fr-profiles.show', $record))
            ->assertOk()
            ->assertSee('Cancel FR')
            ->assertSee('This action cannot be undone.')
            ->assertSee('JAPIC certification')
            ->assertSee('Not Available');

        $counts = $this->protectedRecordCounts();
        $response = $this->actingAs($actor)->post(route('ib39.fr-profiles.cancel', $record), [
            'reason' => '  Approved operational cancellation reason.  ',
            'confirmed' => '1',
        ]);

        $response->assertRedirect(route('ib39.fr-profiles.show', $record));
        $cancellation = Ib39FrCancellation::query()->sole();
        $this->assertSame('Approved operational cancellation reason.', $cancellation->reason);
        $this->assertSame($actor->id, $cancellation->cancelled_by);
        $this->assertSame(Ib39SurfacedFormerRebel::OVERALL_CASE_STATUS, $cancellation->previous_overall_status);
        $this->assertNotNull($cancellation->cancelled_at);
        $this->assertNotSame(
            $cancellation->reason,
            DB::table('ib39_fr_cancellations')->value('reason'),
        );
        $this->assertSame($counts, $this->protectedRecordCounts());
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $actor->id,
            'action' => 'ib39_surfaced_former_rebel_cancelled',
            'entity_type' => Ib39SurfacedFormerRebel::class,
            'entity_id' => $record->id,
        ]);
        $this->assertArrayNotHasKey('reason', AuditLog::query()->latest('id')->firstOrFail()->new_values);

        $this->actingAs($actor)->get(route('ib39.fr-profiles.show', $record))
            ->assertOk()
            ->assertSee('Cancelled')
            ->assertSee('Approved operational cancellation reason.')
            ->assertDontSee('data-bs-target="#cancelFrModal"', false);
    }

    public function test_reason_and_confirmation_are_required_bounded_trimmed_and_server_fields_are_rejected(): void
    {
        $actor = User::factory()->role('39th_ib')->create();
        $record = $this->record($actor);
        $route = route('ib39.fr-profiles.cancel', $record);

        $this->actingAs($actor)->post($route, ['reason' => ''])->assertSessionHasErrors(['reason', 'confirmed']);
        $this->actingAs($actor)->post($route, [
            'reason' => str_repeat('x', 2001),
            'confirmed' => '1',
        ])->assertSessionHasErrors('reason');

        foreach (['cancelled_by', 'cancelled_at', 'previous_overall_status', 'status'] as $field) {
            $this->actingAs($actor)->post($route, [
                'reason' => 'Valid reason',
                'confirmed' => '1',
                $field => 'server-owned',
            ])->assertSessionHasErrors($field);
        }

        $this->assertDatabaseCount('ib39_fr_cancellations', 0);
    }

    public function test_guests_other_roles_and_inactive_39th_ib_users_cannot_cancel(): void
    {
        $owner = User::factory()->role('39th_ib')->create();
        $record = $this->record($owner);
        $payload = ['reason' => 'Not authorized', 'confirmed' => '1'];
        $route = route('ib39.fr-profiles.cancel', $record);

        $this->post($route, $payload)->assertRedirect(route('login'));

        foreach (['japic', 'mblrc', 'admin', 'super_admin', 'lgu', 'afp', 'pnp'] as $role) {
            $this->actingAs(User::factory()->role($role)->create())
                ->post($route, $payload)
                ->assertForbidden();
        }

        $inactive = User::factory()->role('39th_ib')->create(['is_active' => false]);
        $this->assertFalse($inactive->can('cancel', $record));
        $this->actingAs($inactive)->post($route, $payload)->assertRedirect(route('login'));
        $this->assertDatabaseCount('ib39_fr_cancellations', 0);
    }

    public function test_soft_deleted_and_missing_records_return_404(): void
    {
        $actor = User::factory()->role('39th_ib')->create();
        $record = $this->record($actor);
        $record->delete();

        $payload = ['reason' => 'Valid cancellation reason', 'confirmed' => '1'];
        $this->actingAs($actor)->post("/39th-ib/fr-profiles/{$record->id}/cancel", $payload)->assertNotFound();
        $this->actingAs($actor)->post('/39th-ib/fr-profiles/999999/cancel', $payload)->assertNotFound();
    }

    public function test_concurrent_duplicate_attempts_leave_exactly_one_immutable_cancellation(): void
    {
        $actor = User::factory()->role('39th_ib')->create();
        $record = $this->record($actor);
        $service = app(Ib39SurfacedFormerRebelCancellationService::class);

        $service->cancel($record, 'First approved reason', $actor);

        try {
            DB::table('ib39_fr_cancellations')->insert([
                'ib39_surfaced_former_rebel_id' => $record->id,
                'previous_overall_status' => Ib39SurfacedFormerRebel::OVERALL_CASE_STATUS,
                'reason' => 'Simulated racing request',
                'cancelled_by' => $actor->id,
                'cancelled_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->fail('The unique constraint accepted a racing cancellation insert.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }

        try {
            $service->cancel($record, 'Concurrent duplicate reason', $actor);
            $this->fail('A duplicate cancellation was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('reason', $exception->errors());
        }

        $this->assertDatabaseCount('ib39_fr_cancellations', 1);
        $cancellation = Ib39FrCancellation::query()->sole();
        $this->assertSame('First approved reason', $cancellation->reason);
        $this->expectException(\LogicException::class);
        $cancellation->update(['reason' => 'Changed']);
    }

    public function test_cancellation_transitions_incomplete_japic_processing_but_preserves_completed_processing(): void
    {
        $actor = User::factory()->role('39th_ib')->create();
        $pendingRecord = $this->record($actor, 'Pending');
        $pending = $this->japicProcessing($pendingRecord, $actor, JapicCertificationStatus::Drafting);

        app(Ib39SurfacedFormerRebelCancellationService::class)
            ->cancel($pendingRecord, 'Cancel incomplete certification', $actor);

        $this->assertSame(JapicCertificationStatus::Cancelled, $pending->refresh()->status);
        $this->assertDatabaseHas('japic_certification_histories', [
            'japic_certification_processing_id' => $pending->id,
            'from_status' => JapicCertificationStatus::Drafting->value,
            'to_status' => JapicCertificationStatus::Cancelled->value,
            'event' => 'parent_fr_cancelled',
        ]);

        $completedRecord = $this->record($actor, 'Completed');
        $completed = $this->japicProcessing($completedRecord, $actor, JapicCertificationStatus::Completed);
        app(Ib39SurfacedFormerRebelCancellationService::class)
            ->cancel($completedRecord, 'Cancel completed certification parent', $actor);

        $this->assertSame(JapicCertificationStatus::Completed, $completed->refresh()->status);
        $this->assertDatabaseCount('japic_certification_histories', 1);
    }

    public function test_audit_failure_rolls_back_cancellation_and_japic_transition(): void
    {
        $actor = User::factory()->role('39th_ib')->create();
        $record = $this->record($actor);
        $processing = $this->japicProcessing($record, $actor, JapicCertificationStatus::Pending);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER fail_fr_cancellation_audit
            BEFORE INSERT ON audit_logs
            WHEN NEW.action = 'ib39_surfaced_former_rebel_cancelled'
            BEGIN
                SELECT RAISE(ABORT, 'simulated cancellation audit failure');
            END
        SQL);

        try {
            app(Ib39SurfacedFormerRebelCancellationService::class)
                ->cancel($record, 'Rollback this cancellation', $actor);
            $this->fail('The simulated audit failure did not abort cancellation.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('simulated cancellation audit failure', $exception->getMessage());
        }

        $this->assertDatabaseCount('ib39_fr_cancellations', 0);
        $this->assertDatabaseCount('japic_certification_histories', 0);
        $this->assertSame(JapicCertificationStatus::Pending, $processing->refresh()->status);
    }

    private function record(User $actor, string $suffix = 'Record'): Ib39SurfacedFormerRebel
    {
        $municipality = Municipality::query()->firstOrCreate(['name' => 'Cancellation Test Municipality']);

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

    private function japicProcessing(
        Ib39SurfacedFormerRebel $record,
        User $actor,
        JapicCertificationStatus $status,
    ): JapicCertificationProcessing {
        $cdr = $record->cdrProcessing()->firstOrFail();
        $version = $cdr->documentVersions()->create([
            'version_number' => 1,
            'source_type' => Ib39CdrDocumentSource::Generated,
            'storage_path' => "generated/cdr/{$cdr->id}/version/1",
            'original_filename' => 'generated-cdr-v1.html',
            'mime_type' => 'text/html',
            'size_bytes' => 10,
            'sha256' => str_repeat('a', 64),
            'content_schema_version' => 1,
            'content_snapshot' => [],
            'created_by' => $actor->id,
            'finalized_at' => now(),
        ]);
        $cdr->update([
            'status' => 'Completed',
            'completed_at' => now(),
            'completed_by' => $actor->id,
            'current_final_version_id' => $version->id,
        ]);

        return JapicCertificationProcessing::query()->forceCreate([
            'ib39_surfaced_former_rebel_id' => $record->id,
            'triggering_cdr_document_version_id' => $version->id,
            'status' => $status,
            'received_on' => '2026-09-01',
            'due_on' => '2026-09-15',
            'trigger_source' => JapicCertificationTriggerSource::CdrCompletion,
            'completed_at' => $status === JapicCertificationStatus::Completed ? now() : null,
        ]);
    }

    private function protectedRecordCounts(): array
    {
        return [
            'fr' => DB::table('ib39_surfaced_former_rebels')->count(),
            'cdr' => DB::table('ib39_cdr_processings')->count(),
            'cdr_forms' => DB::table('ib39_cdr_forms')->count(),
            'fea' => DB::table('ib39_fea_processings')->count(),
            'fea_documents' => DB::table('ib39_fea_documents')->count(),
        ];
    }
}
