<?php

namespace Tests\Feature;

use App\Models\GovAgency;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_role_can_render_its_primary_modules_without_server_errors(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Smoke Test Municipality']);
        $agency = GovAgency::query()->create([
            'name' => 'Smoke Test Agency',
            'acronym' => 'STA',
        ]);

        $rolesAndRoutes = [
            'super_admin' => [
                'super_admin.dashboard',
                'super_admin.users.index',
                'super_admin.agencies.index',
                'super_admin.audit-logs.index',
            ],
            'admin' => [
                'admin.dashboard',
                'admin.rcsp.index',
                'admin.implan.index',
                'admin.agencies.index',
                'admin.locations.index',
                'admin.clusters.index',
                'admin.users.index',
            ],
            '39th_ib' => [
                'ib39.dashboard',
                'ib39.areas.index',
                'ib39.map',
                'ib39.map.data',
            ],
            'lgu' => [
                'lgu.dashboard',
                'lgu.rcsp.index',
                'lgu.evaluation.index',
                'lgu.implan.index',
            ],
            'gov_agency' => [
                'gov_agency.dashboard',
                'gov_agency.implan.index',
            ],
            'mblrc' => [
                'mblrc.dashboard',
                'mblrc.analytics',
                'mblrc.statistics',
                'mblrc.fr.index',
                'mblrc.fr.create',
                'mblrc.fr.locations',
                'mblrc.barangays',
                'mblrc.fr.skills.suggestions',
            ],
            'afp' => [
                'afp.dashboard',
                'afp.rcsp.index',
            ],
        ];

        foreach ($rolesAndRoutes as $role => $routeNames) {
            $user = User::factory()->role($role)->create([
                'municipality_id' => $role === 'lgu' ? $municipality->id : null,
                'gov_agency_id' => $role === 'gov_agency' ? $agency->id : null,
            ]);

            foreach ($routeNames as $routeName) {
                $parameters = $routeName === 'mblrc.barangays'
                    ? ['municipality_id' => $municipality->id]
                    : [];

                $this->actingAs($user)
                    ->get(route($routeName, $parameters))
                    ->assertSuccessful();
            }
        }
    }
}
