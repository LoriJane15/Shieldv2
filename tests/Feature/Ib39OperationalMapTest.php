<?php

namespace Tests\Feature;

use App\Models\FormerRebel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Ib39OperationalMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_39th_ib_user_can_view_operational_map(): void
    {
        $user = User::factory()->role('39th_ib')->create();

        $this->actingAs($user)
            ->get(route('ib39.map'))
            ->assertOk()
            ->assertSee('Former Rebel Locations')
            ->assertSee('data-map-search', false)
            ->assertSee(route('ib39.map.data'), false);
    }

    public function test_map_data_returns_only_records_with_valid_coordinates(): void
    {
        $user = User::factory()->role('39th_ib')->create();
        $mapped = FormerRebel::query()->create([
            'classified_id' => 'FR-#1001',
            'firstname' => 'Mapped',
            'lastname' => 'Record',
            'latitude' => 6.7497,
            'longitude' => 125.3572,
            'status' => 'Active',
        ]);
        FormerRebel::query()->create([
            'classified_id' => 'FR-#1002',
            'firstname' => 'Unmapped',
            'lastname' => 'Record',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ib39.map.data'));

        $response->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private')
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('markers.0.id', $mapped->id)
            ->assertJsonPath('markers.0.name', 'Mapped Record')
            ->assertJsonMissing(['name' => 'Unmapped Record']);
    }

    public function test_map_data_can_be_filtered_by_status(): void
    {
        $user = User::factory()->role('39th_ib')->create();
        $this->formerRebel('FR-#1001', 'Active', 6.7497);
        $this->formerRebel('FR-#1002', 'Reintegrated', 6.7597);

        $this->actingAs($user)
            ->getJson(route('ib39.map.data', ['status' => 'Reintegrated']))
            ->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('markers.0.status', 'Reintegrated');
    }

    public function test_other_roles_cannot_access_39th_ib_map_data(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->getJson(route('ib39.map.data'))
            ->assertForbidden();
    }

    private function formerRebel(string $classifiedId, string $status, float $latitude): FormerRebel
    {
        return FormerRebel::query()->create([
            'classified_id' => $classifiedId,
            'firstname' => 'Synthetic',
            'lastname' => $status,
            'latitude' => $latitude,
            'longitude' => 125.3572,
            'status' => $status,
        ]);
    }
}
