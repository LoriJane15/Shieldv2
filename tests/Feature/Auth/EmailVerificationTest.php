<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_is_not_part_of_the_account_workflow(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/verify-email')->assertNotFound();

        $this->assertFalse(Route::has('verification.verify'));
        $this->assertFalse(Route::has('verification.send'));
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
