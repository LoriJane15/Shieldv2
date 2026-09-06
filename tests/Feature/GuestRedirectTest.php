<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class GuestRedirectTest extends TestCase
{
    /**
     * Signed-in users clicking "Log in" on the public landing page must reach
     * their own area, not bounce back to "/" (Laravel's fallback when no route
     * is named plainly "dashboard").
     */
    public function test_authenticated_users_hitting_login_go_to_their_area(): void
    {
        $this->skipUnlessLegacyDataPresent();

        $expected = [
            'admin' => '/katuparan',
            'lgu' => '/lgu',
            'super_admin' => '/super-admin',
            'mblrc' => '/mblrc',
            '39th_ib' => '/39th-ib',
            'gov_agency' => '/agency',
            'afp' => '/afp',
        ];

        foreach ($expected as $role => $path) {
            $user = User::where('role', $role)->first();
            if (! $user) {
                continue;
            }

            $this->actingAs($user)
                ->get('/login')
                ->assertRedirect($path);
        }
    }

    public function test_guests_still_see_the_login_form(): void
    {
        $this->get('/login')->assertOk()->assertSee('Username', false);
    }
}
