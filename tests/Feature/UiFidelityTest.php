<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UiFidelityTest extends TestCase
{
    /** Every authenticated page must render the SkyDash chrome, not Breeze/Tailwind. */
    public function test_pages_render_skydash_chrome(): void
    {
        $this->skipUnlessLegacyDataPresent();

        $pages = [
            ['admin', '/katuparan'],
            ['lgu', '/lgu'],
            ['super_admin', '/super-admin'],
            ['mblrc', '/mblrc'],
            ['39th_ib', '/39th-ib'],
            ['gov_agency', '/agency'],
            ['afp', '/afp'],
            ['lgu', '/profile'],
        ];

        foreach ($pages as [$role, $uri]) {
            $user = User::where('role', $role)->firstOrFail();
            $html = $this->actingAs($user)->get($uri)->assertOk()->getContent();

            $this->assertStringContainsString('container-scroller', $html, "$uri missing SkyDash shell");

            // Katuparan + AFP use the horizontal nav, as they did in the legacy
            // app; every other role uses the vertical sidebar.
            $this->assertMatchesRegularExpression(
                '#assets/css/(vertical|horizontal)-layout-light/style\.css#',
                $html,
                "$uri missing a SkyDash layout stylesheet"
            );
            $this->assertStringNotContainsString('x-guest-layout', $html);
        }
    }

    public function test_login_and_confirm_password_are_skydash(): void
    {
        $this->skipUnlessLegacyDataPresent();

        $login = $this->get('/login')->assertOk()->getContent();
        $this->assertStringContainsString('auth-form-transparent', $login);

        $confirm = $this->actingAs(User::firstOrFail())->get('/confirm-password')->assertOk()->getContent();
        $this->assertStringContainsString('auth-form-transparent', $confirm, 'confirm-password must match login');
        $this->assertStringNotContainsString('text-gray-', $confirm, 'no Breeze Tailwind left');
    }

    public function test_destructive_actions_use_sweetalert_not_native_confirm(): void
    {
        $this->skipUnlessLegacyDataPresent();

        $user = User::where('role', 'super_admin')->firstOrFail();
        $html = $this->actingAs($user)->get('/super-admin/users')->assertOk()->getContent();

        $this->assertStringContainsString('data-confirm=', $html);
        $this->assertStringNotContainsString('return confirm(', $html);
        $this->assertStringContainsString('sweetalert.min.js', $html);
    }

    /** The SkyDash layouts must load the Vite bundle — every map, cascade and
     *  confirm dialog lives in it. Without this the pages render but do nothing. */
    public function test_layouts_load_the_app_js_bundle(): void
    {
        $this->skipUnlessLegacyDataPresent();

        foreach ([['admin', '/katuparan'], ['39th_ib', '/39th-ib/map'], ['39th_ib', '/39th-ib/areas'], ['lgu', '/lgu']] as [$role, $uri]) {
            $user = User::where('role', $role)->firstOrFail();
            $html = $this->actingAs($user)->get($uri)->assertOk()->getContent();

            $this->assertMatchesRegularExpression(
                '#build/assets/app-[A-Za-z0-9_-]+.js#',
                $html,
                "$uri does not load the app JS bundle"
            );
        }
    }
}
