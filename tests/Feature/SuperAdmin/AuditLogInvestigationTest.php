<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\AuditLog;
use App\Models\GovAgency;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogInvestigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_safe_investigation_metadata_and_actor_context(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Investigation Municipality']);
        $actor = User::factory()->role('lswdo')->create([
            'name' => 'Audit Investigation Actor',
            'municipality_id' => $municipality->id,
        ]);
        $log = $this->log($actor, [
            'action' => 'updated_case',
            'entity_type' => GovAgency::class,
            'entity_id' => 918,
            'previous_values' => ['confidential_narrative' => 'Previous protected value'],
            'new_values' => ['confidential_narrative' => 'New protected value'],
            'ip_address' => '192.0.2.15',
            'user_agent' => 'Synthetic Browser on Test Platform',
        ]);

        $response = $this->actingAs(User::factory()->role('super_admin')->create())
            ->get(route('super_admin.audit-logs.index'));

        $response->assertOk()
            ->assertSee('Audit Investigation Actor')
            ->assertSee('LSWDO · Investigation Municipality')
            ->assertSee('UPDATED CASE')
            ->assertSee('Government Agencies')
            ->assertSee($log->eventReference())
            ->assertSee('192.0.2.15')
            ->assertSee('Synthetic Browser on Test Platform')
            ->assertDontSee('Previous protected value')
            ->assertDontSee('New protected value')
            ->assertDontSee('previous_values')
            ->assertDontSee('new_values');
    }

    public function test_filters_use_recorded_metadata_and_preserve_query_parameters(): void
    {
        $lswdo = User::factory()->role('lswdo')->create(['name' => 'Matching Audit Actor']);
        $admin = User::factory()->role('admin')->create(['name' => 'Excluded Audit Actor']);
        $matching = $this->log($lswdo, [
            'action' => 'updated_case',
            'entity_type' => GovAgency::class,
            'entity_id' => 123,
            'created_at' => '2026-08-09 05:00:00',
            'updated_at' => '2026-08-09 05:00:00',
        ]);
        $this->log($admin, [
            'action' => 'agency_updated',
            'entity_type' => GovAgency::class,
            'entity_id' => 456,
            'created_at' => '2026-07-01 05:00:00',
            'updated_at' => '2026-07-01 05:00:00',
        ]);

        $response = $this->actingAs(User::factory()->role('super_admin')->create())->get(route('super_admin.audit-logs.index', [
            'search' => $matching->eventReference(),
            'action' => 'updated_case',
            'module' => GovAgency::class,
            'role' => 'lswdo',
            'date_range' => 'custom',
            'date_from' => '2026-08-09',
            'date_to' => '2026-08-09',
            'sort' => 'oldest',
            'per_page' => 50,
        ]));

        $response->assertOk()
            ->assertSee('Matching Audit Actor')
            ->assertDontSee('Excluded Audit Actor')
            ->assertSee('value="custom" selected', false)
            ->assertSee('value="50" selected', false)
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 1);
    }

    public function test_custom_date_range_is_validated_on_the_server(): void
    {
        $response = $this->actingAs(User::factory()->role('super_admin')->create())
            ->get(route('super_admin.audit-logs.index', [
                'date_range' => 'custom',
                'date_from' => '2026-08-10',
                'date_to' => '2026-08-09',
            ]));

        $response->assertSessionHasErrors('date_to');
    }

    public function test_logs_are_paginated_newest_first_without_loading_the_full_table(): void
    {
        $actor = User::factory()->role('admin')->create();

        foreach (range(1, 30) as $index) {
            $this->log($actor, [
                'action' => 'agency_updated',
                'entity_type' => GovAgency::class,
                'entity_id' => $index,
                'created_at' => now()->subMinutes(30 - $index),
                'updated_at' => now()->subMinutes(30 - $index),
            ]);
        }

        $response = $this->actingAs(User::factory()->role('super_admin')->create())
            ->get(route('super_admin.audit-logs.index'));

        $response->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->count() === 25
                && $logs->total() === 30
                && $logs->first()->entity_id === 30
                && $logs->last()->entity_id === 6);
    }

    private function log(User $actor, array $attributes): AuditLog
    {
        $timestamps = array_filter([
            'created_at' => $attributes['created_at'] ?? null,
            'updated_at' => $attributes['updated_at'] ?? null,
        ]);
        unset($attributes['created_at'], $attributes['updated_at']);

        $log = AuditLog::query()->create([
            'user_id' => $actor->id,
            'action' => 'updated',
            'entity_type' => User::class,
            'entity_id' => $actor->id,
            'ip_address' => '127.0.0.1',
            ...$attributes,
        ]);

        if ($timestamps !== []) {
            $log->forceFill($timestamps)->saveQuietly();
        }

        return $log;
    }
}
