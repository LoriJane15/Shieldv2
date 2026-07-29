<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200);
    }

    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->role('39th_ib')->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect(route('ib39.dashboard'));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('auth.password_confirmed_at');
    }

    public function test_password_confirmation_honors_the_intended_url(): void
    {
        $user = User::factory()->role('admin')->create();

        $response = $this->actingAs($user)
            ->withSession(['url.intended' => route('profile.edit')])
            ->post('/confirm-password', ['password' => 'password']);

        $response->assertRedirect(route('profile.edit'));
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
}
