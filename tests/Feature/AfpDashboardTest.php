<?php

namespace Tests\Feature;

use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AfpDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_afp_dashboard_displays_the_digos_city_seal_and_responsive_map(): void
    {
        Municipality::query()->create(['name' => 'Digos City']);
        $afpUser = User::factory()->role('afp')->create();

        $this->actingAs($afpUser)
            ->get(route('afp.dashboard'))
            ->assertSuccessful()
            ->assertSee('assets/LGUS/digos.png', false)
            ->assertSee('ResizeObserver', false)
            ->assertSee('Former Rebel Status', false)
            ->assertSee('Under Review', false);
    }
}
