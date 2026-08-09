<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\GovAgency;
use App\Models\Implementation;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_access_user_management(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->get(route('super_admin.users.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_role_scoped_users(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $municipality = Municipality::query()->create(['name' => 'Test Municipality']);
        $agency = GovAgency::query()->create(['name' => 'Test Agency', 'acronym' => 'TA']);

        $this->actingAs($superAdmin)->post(route('super_admin.users.store'), [
            'name' => 'LGU Test User',
            'username' => 'lgu_scoped',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'role' => 'lgu',
            'municipality_id' => $municipality->id,
            'gov_agency_id' => $agency->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'username' => 'lgu_scoped',
            'role' => 'lgu',
            'municipality_id' => $municipality->id,
            'gov_agency_id' => null,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_can_deactivate_an_account_but_not_their_own(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $user = User::factory()->role('admin')->create();

        $this->actingAs($superAdmin)->put(route('super_admin.users.update', $user), [
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'is_active' => false,
        ])->assertSessionHasNoErrors();

        $this->assertFalse($user->fresh()->is_active);

        $this->actingAs($superAdmin)->put(route('super_admin.users.update', $superAdmin), [
            'name' => $superAdmin->name,
            'username' => $superAdmin->username,
            'role' => 'super_admin',
            'is_active' => false,
        ])->assertUnprocessable();

        $this->assertTrue($superAdmin->fresh()->is_active);
    }

    public function test_role_scope_and_username_are_validated(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        User::factory()->create(['username' => 'already_used']);

        $this->actingAs($superAdmin)->post(route('super_admin.users.store'), [
            'name' => 'Invalid User',
            'username' => 'already_used',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'role' => 'gov_agency',
        ])->assertSessionHasErrors(['username', 'gov_agency_id']);
    }

    public function test_role_filter_remains_applied_when_searching(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $matchingLgu = User::factory()->role('lgu')->create([
            'name' => 'Shared Search Name',
            'username' => 'matching_lgu',
        ]);
        User::factory()->role('admin')->create([
            'name' => 'Shared Search Name',
            'username' => 'wrong_role',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('super_admin.users.index', [
            'search' => 'Shared Search Name',
            'role' => 'lgu',
        ]));

        $users = $response->viewData('users');

        $response->assertOk();
        $this->assertSame([$matchingLgu->id], $users->pluck('id')->all());
    }

    public function test_super_admin_cannot_remove_their_own_super_admin_role(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();

        $this->actingAs($superAdmin)->put(route('super_admin.users.update', $superAdmin), [
            'name' => $superAdmin->name,
            'username' => $superAdmin->username,
            'role' => 'admin',
        ])->assertUnprocessable();

        $this->assertSame('super_admin', $superAdmin->fresh()->role);
    }

    public function test_user_with_official_workflow_records_cannot_be_deleted(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $lgu = User::factory()->role('lgu')->create();
        $implementation = Implementation::query()->create([
            'lgu_user_id' => $lgu->id,
            'issues' => 'Official test workflow record',
        ]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.users.destroy', $lgu))
            ->assertUnprocessable();

        $this->assertDatabaseHas('users', ['id' => $lgu->id]);
        $this->assertDatabaseHas('implementations', ['id' => $implementation->id]);
    }

    public function test_unused_user_can_be_deleted_but_super_admin_cannot_delete_self(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $unused = User::factory()->role('admin')->create();

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.users.destroy', $unused))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('users', ['id' => $unused->id]);

        $this->actingAs($superAdmin)
            ->delete(route('super_admin.users.destroy', $superAdmin))
            ->assertForbidden();
    }

    public function test_user_delete_action_uses_confirmation_modal(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $unused = User::factory()->role('admin')->create();

        $this->actingAs($superAdmin)
            ->get(route('super_admin.users.index'))
            ->assertOk()
            ->assertSee('id="deleteConfirmationModal"', false)
            ->assertSee('data-delete-confirm', false)
            ->assertSee('data-delete-acknowledgment', false)
            ->assertSee('I understand that this record will be permanently deleted.')
            ->assertSee(route('super_admin.users.destroy', $unused), false)
            ->assertSee('new bootstrap.Modal', false)
            ->assertDontSee('getOrCreateInstance', false)
            ->assertDontSee("return confirm('", false);
    }
}
