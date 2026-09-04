<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_landing_page_displays_login_action(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('Log in')
            ->assertDontSee('My Account')
            ->assertDontSee('Log out');
    }

    public function test_authenticated_japic_sees_account_and_post_logout_actions_instead_of_guest_login(): void
    {
        $user = User::factory()->role('japic')->create();

        $this->actingAs($user)
            ->get(route('landing'))
            ->assertOk()
            ->assertSee('href="'.route('profile.edit').'"', false)
            ->assertSee('My Account')
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('method="POST"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('Log out')
            ->assertDontSee('href="'.route('login').'"', false);
    }

    public function test_authenticated_japic_login_redirect_has_no_cycle(): void
    {
        $user = User::factory()->role('japic')->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('profile.edit'));

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Account');
    }

    public function test_authenticated_japic_logout_ends_the_session_and_redirects_to_login(): void
    {
        $user = User::factory()->role('japic')->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('href="'.route('login').'"', false)
            ->assertDontSee('My Account')
            ->assertDontSee('Log out');
    }

    public function test_retained_removed_module_roles_redirect_to_an_authenticated_destination(): void
    {
        $roles = [
            'lswdo',
            'japic',
            'eclip_assessor',
            'dilg_reviewer',
            'eclip_funding_officer',
            'dilg_provincial_focal',
            'dilg_regional',
            'nboo_eclip_pmo',
            'dilg_fms',
            'local_eclip_committee',
            'pnp',
        ];

        foreach ($roles as $role) {
            $user = User::factory()->role($role)->create();

            $this->actingAs($user)
                ->get(route('login'))
                ->assertRedirect(route('profile.edit'));

            auth()->logout();
        }
    }

    public function test_every_role_has_a_registered_non_public_authenticated_destination(): void
    {
        $roles = [
            ...array_keys(config('shield.roles')),
            'eclip_assessor',
            'dilg_reviewer',
            'eclip_funding_officer',
            'unexpected_legacy_role',
        ];

        foreach (array_unique($roles) as $role) {
            $user = User::factory()->role($role)->create();
            $homeRoute = $user->homeRoute();

            $this->assertNotContains($homeRoute, ['landing', 'login'], $role);
            $this->assertTrue(app('router')->has($homeRoute), $role);

            $this->actingAs($user)
                ->get(route('login'))
                ->assertRedirect(route($homeRoute));

            auth()->logout();
        }
    }

    public function test_guest_login_action_opens_the_login_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('method="POST"', false)
            ->assertSee('action="'.route('login').'"', false)
            ->assertSee('name="username"', false)
            ->assertSee('name="password"', false);
    }
}
