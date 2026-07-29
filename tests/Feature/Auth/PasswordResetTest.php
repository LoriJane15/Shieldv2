<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_email_password_reset_is_disabled(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->get('/forgot-password')->assertNotFound();
        $this->post('/forgot-password', ['email' => $user->email])->assertNotFound();

        $this->assertFalse(Route::has('password.request'));
        $this->assertFalse(Route::has('password.email'));
        Notification::assertNothingSent();
    }

    public function test_login_explains_the_super_admin_recovery_process(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Forgot password? Contact your Super Admin.');
    }
}
