<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\FormerRebel;
use App\Models\GovAgency;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePrivacyNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_katuparan_monitoring_pages_do_not_expose_beneficiary_map_data(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Privacy Municipality']);
        FormerRebel::query()->create([
            'classified_id' => 'FR-#PRIVATE',
            'firstname' => 'ConfidentialFirst',
            'lastname' => 'ConfidentialLast',
            'placement_address' => 'Confidential Placement Address',
            'municipality_id' => $municipality->id,
            'latitude' => 6.12345678,
            'longitude' => 125.12345678,
        ]);
        $admin = User::factory()->role('admin')->create();

        foreach (['admin.dashboard', 'admin.locations.index'] as $routeName) {
            $this->actingAs($admin)->get(route($routeName))
                ->assertOk()
                ->assertDontSee('ConfidentialFirst')
                ->assertDontSee('ConfidentialLast')
                ->assertDontSee('Confidential Placement Address')
                ->assertDontSee('6.12345678')
                ->assertDontSee('125.12345678');
        }
    }

    public function test_role_navigation_shows_consolidated_modules(): void
    {
        $expectations = [
            'super_admin' => ['User Management', 'Government Agencies', 'General Audit Logs'],
            'admin' => ['SHIELD Monitoring', 'Cluster Monitoring', 'Contribution Monitoring'],
            'mblrc' => ['FR/FVE Registry'],
        ];

        foreach ($expectations as $role => $labels) {
            $user = User::factory()->role($role)->create();
            $response = $this->actingAs($user)->get(route($user->homeRoute()))->assertOk();

            foreach ($labels as $label) {
                $response->assertSee($label);
            }
        }
    }

    public function test_shield_workspace_shell_is_shared_by_operational_roles(): void
    {
        $mblrc = User::factory()->role('mblrc')->create();

        $this->actingAs($mblrc)->get(route('mblrc.dashboard'))
            ->assertOk()
            ->assertSee('assets/css/mblrc-workspace.css', false)
            ->assertSee('mblrc-interface', false)
            ->assertSee('mblrc-navbar-search', false)
            ->assertSee('Toggle sidebar navigation')
            ->assertDontSee('icon-bell menu-icon', false)
            ->assertDontSee('Secure workspace')
            ->assertDontSee('MBLRC Workspace');

        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('assets/css/mblrc-workspace.css', false)
            ->assertDontSee('mblrc-interface', false)
            ->assertDontSee('shield-role-interface', false)
            ->assertDontSee('mblrc-navbar-search', false);
    }

    public function test_removed_operational_modules_are_not_in_role_navigation(): void
    {
        $admin = User::factory()->role('admin')->create();
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertDontSee('System Analytics')
            ->assertDontSee('Users', false);
    }

    public function test_super_admin_can_view_sanitized_general_audit_log_metadata(): void
    {
        $actor = User::factory()->role('lswdo')->create();
        $log = AuditLog::query()->create([
            'user_id' => $actor->id,
            'action' => 'updated_case',
            'entity_type' => GovAgency::class,
            'entity_id' => 918,
            'previous_values' => ['confidential_narrative' => 'Previous secret'],
            'new_values' => ['confidential_narrative' => 'New secret'],
            'ip_address' => '127.0.0.1',
        ]);
        $superAdmin = User::factory()->role('super_admin')->create();

        $this->actingAs($superAdmin)->get(route('super_admin.audit-logs.index'))
            ->assertOk()
            ->assertSee($actor->name)
            ->assertSee("#{$log->entity_id}")
            ->assertDontSee('Previous secret')
            ->assertDontSee('New secret');

        $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('super_admin.audit-logs.index'))
            ->assertForbidden();
    }
}
