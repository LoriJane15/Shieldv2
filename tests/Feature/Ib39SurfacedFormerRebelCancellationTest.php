<?php

namespace Tests\Feature;

use App\Enums\Ib39FeaDocumentStatus;
use App\Enums\Ib39FrCategory;
use App\Models\AuditLog;
use App\Models\Ib39FrCancellation;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\Municipality;
use App\Models\User;
use App\Services\Ib39SurfacedFormerRebelCancellationService;
use App\Services\Ib39SurfacedFormerRebelService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

class Ib39SurfacedFormerRebelCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_39th_ib_user_can_cancel_without_deleting_related_data(): void
    {
        [$actor, $record] = $this->context();
        $counts = $this->protectedCounts();

        $this->actingAs($actor)->post(route('ib39.fr-profiles.cancel', $record), [
            'reason' => '  Approved operational cancellation reason.  ',
            'confirmed' => '1',
        ])->assertRedirect(route('ib39.fr-profiles.show', $record));

        $cancellation = Ib39FrCancellation::query()->sole();
        $this->assertSame('Approved operational cancellation reason.', $cancellation->reason);
        $this->assertSame($actor->id, $cancellation->cancelled_by);
        $this->assertSame($counts, $this->protectedCounts());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ib39_surfaced_former_rebel_cancelled',
            'entity_id' => $record->id,
        ]);
        $this->assertArrayNotHasKey('reason', AuditLog::query()->latest('id')->firstOrFail()->new_values);
    }

    public function test_validation_and_authorization_are_enforced(): void
    {
        [$actor, $record] = $this->context();
        $route = route('ib39.fr-profiles.cancel', $record);

        $this->post($route, [])->assertRedirect(route('login'));
        foreach (['super_admin', 'admin', 'lgu', 'gov_agency', 'mblrc', 'afp'] as $role) {
            $this->actingAs(User::factory()->role($role)->create())
                ->post($route, ['reason' => 'Not authorized', 'confirmed' => '1'])
                ->assertForbidden();
        }
        $this->actingAs($actor)->post($route, ['reason' => ''])->assertSessionHasErrors(['reason', 'confirmed']);
        $this->actingAs($actor)->post($route, [
            'reason' => str_repeat('x', 2001),
            'confirmed' => '1',
        ])->assertSessionHasErrors('reason');
        foreach (['cancelled_by', 'cancelled_at', 'previous_overall_status', 'status'] as $field) {
            $this->actingAs($actor)->post($route, [
                'reason' => 'Valid reason', 'confirmed' => '1', $field => 'server-owned',
            ])->assertSessionHasErrors($field);
        }
        $this->assertDatabaseCount('ib39_fr_cancellations', 0);
    }

    public function test_duplicate_cancellation_is_rejected_and_history_is_immutable(): void
    {
        [$actor, $record] = $this->context();
        $service = app(Ib39SurfacedFormerRebelCancellationService::class);
        $service->cancel($record, 'First approved reason', $actor);

        try {
            $service->cancel($record, 'Duplicate reason', $actor);
            $this->fail('A duplicate cancellation was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('reason', $exception->errors());
        }

        $this->assertDatabaseCount('ib39_fr_cancellations', 1);
        $this->expectException(LogicException::class);
        Ib39FrCancellation::query()->sole()->update(['reason' => 'Changed']);
    }

    public function test_cancellation_blocks_cdr_and_fea_mutation_and_reads_no_japic_tables(): void
    {
        [$actor, $record] = $this->context();
        $cdr = $record->cdrProcessing()->firstOrFail();
        $fea = $record->feaProcessing()->firstOrFail();
        $document = $fea->documents()->firstOrFail();
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = strtolower($query->sql);
        });

        app(Ib39SurfacedFormerRebelCancellationService::class)
            ->cancel($record, 'Approved cancellation', $actor);

        $this->actingAs($actor)->post(route('ib39.cdr.start', $cdr))->assertForbidden();
        $this->actingAs($actor)->post(route('ib39.fea.documents.start', [$fea, $document]))->assertForbidden();
        $this->assertSame(Ib39FeaDocumentStatus::Pending, $document->fresh()->status);
        $this->assertFalse(collect($queries)->contains(
            fn (string $sql): bool => str_contains($sql, 'japic'),
        ));
    }

    public function test_audit_failure_rolls_back_cancellation(): void
    {
        [$actor, $record] = $this->context();
        DB::unprepared("CREATE TRIGGER fail_fr_cancellation_audit BEFORE INSERT ON audit_logs WHEN NEW.action = 'ib39_surfaced_former_rebel_cancelled' BEGIN SELECT RAISE(ABORT, 'simulated failure'); END");

        try {
            app(Ib39SurfacedFormerRebelCancellationService::class)->cancel($record, 'Rollback reason', $actor);
            $this->fail('The simulated audit failure did not abort cancellation.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('simulated failure', $exception->getMessage());
        }
        $this->assertDatabaseCount('ib39_fr_cancellations', 0);
    }

    private function context(): array
    {
        $actor = User::factory()->role('39th_ib')->create();
        $municipality = Municipality::query()->create(['name' => 'Cancellation Test Municipality']);
        $record = app(Ib39SurfacedFormerRebelService::class)->create([
            'first_name' => 'Synthetic', 'last_name' => 'Cancellation',
            'category' => Ib39FrCategory::RegularMember->value,
            'province' => Ib39SurfacedFormerRebel::DEFAULT_PROVINCE,
            'municipality_id' => $municipality->id, 'barangay_id' => null,
            'specific_location' => null, 'surfaced_at' => '2026-09-01',
            'possessed_firearms' => true, 'initial_remarks' => null,
        ], $actor);

        return [$actor, $record];
    }

    private function protectedCounts(): array
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
