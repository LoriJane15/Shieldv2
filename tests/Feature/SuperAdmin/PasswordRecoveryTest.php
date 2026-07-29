<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_assign_a_new_password_to_another_user(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $user = User::factory()->role('admin')->create();
        DB::table('sessions')->insert([
            'id' => 'target-user-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test agent',
            'payload' => 'test-payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($superAdmin)
            ->put(route('super_admin.users.update', $user), [
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'password' => 'replacement-password',
                'password_confirmation' => 'replacement-password',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('replacement-password', $user->fresh()->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'target-user-session']);
    }

    public function test_other_roles_cannot_assign_a_new_password(): void
    {
        $admin = User::factory()->role('admin')->create();
        $user = User::factory()->role('lgu')->create();
        $originalPassword = $user->password;

        $this->actingAs($admin)
            ->put(route('super_admin.users.update', $user), [
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'password' => 'unauthorized-password',
                'password_confirmation' => 'unauthorized-password',
            ])
            ->assertForbidden();

        $this->assertSame($originalPassword, $user->fresh()->password);
    }
}
