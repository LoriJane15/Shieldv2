<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\AuditLog;
use App\Models\EclipCase;
use App\Models\FormerRebel;
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

    public function test_technical_administrators_cannot_view_cases_or_download_case_files(): void
    {
        $case = $this->case();

        foreach (['admin', 'super_admin'] as $role) {
            $user = User::factory()->role($role)->create();

            $this->assertFalse($user->can('view', $case));
            $this->assertFalse($user->can('downloadDocument', $case));
            $this->assertFalse($user->can('downloadFundingProof', $case));
            $this->assertFalse($user->can('downloadReleaseAcknowledgment', $case));
        }
    }

    public function test_role_navigation_shows_consolidated_modules(): void
    {
        $expectations = [
            'super_admin' => ['System Analytics', 'General Audit Logs'],
            'admin' => ['SHIELD Monitoring', 'Cluster Analytics', 'Cluster Monitoring', 'Contribution Monitoring'],
            'mblrc' => ['FR/FVE Registry', 'Integration Monitoring', 'E-CLIP Cases'],
            'lswdo' => ['MBLRC Referrals', 'E-CLIP Cases'],
            'japic' => ['Authentication Queue', 'E-CLIP Documents'],
            'pnp' => ['Dashboard', 'FEA Processing'],
            'local_eclip_committee' => ['Assistance Release', 'Analytics'],
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
            ->assertSee('Integration Monitoring')
            ->assertDontSee('icon-bell menu-icon', false)
            ->assertDontSee('Secure workspace')
            ->assertDontSee('MBLRC Workspace');

        foreach ([
            'lswdo' => ['route' => 'lswdo.eclip.index', 'module_route' => 'lswdo.referrals.index', 'heading' => 'Eligibility Review Queue', 'section' => 'Case Management'],
            'japic' => ['route' => 'japic.eclip.index', 'module_route' => 'japic.authentication.index', 'heading' => 'Document Review Queue', 'section' => 'Document Review'],
            'pnp' => ['route' => 'pnp.dashboard', 'module_route' => 'pnp.eclip-fea.index', 'heading' => 'PNP E-CLIP Coordination', 'section' => 'Case Processing'],
            'local_eclip_committee' => ['route' => 'local_eclip.cases.index', 'module_route' => 'local_eclip.cases.index', 'heading' => 'Assistance Release Queue', 'section' => 'Case Management'],
        ] as $role => $expectation) {
            $user = User::factory()->role($role)->create();

            $this->actingAs($user)->get(route($expectation['route']))
                ->assertOk()
                ->assertSee('assets/css/mblrc-workspace.css', false)
                ->assertSee('mblrc-interface shield-role-interface shield-role-'.$role, false)
                ->assertSee('Toggle sidebar navigation')
                ->assertSee($expectation['heading'])
                ->assertSee($expectation['section'])
                ->assertDontSee('System Active')
                ->assertDontSee('mblrc-navbar-search', false);

            $this->actingAs($user)->get(route($expectation['module_route']))
                ->assertOk()
                ->assertSee('shield-module-header', false)
                ->assertSee('shield-module-icon', false)
                ->assertDontSee('System Active');
        }

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
            ->assertDontSee('E-CLIP Documents')
            ->assertDontSee('E-CLIP Assistance')
            ->assertDontSee('Users', false);

        $lswdo = User::factory()->role('lswdo')->create();
        $this->actingAs($lswdo)->get(route('lswdo.referrals.index'))
            ->assertDontSee('E-CLIP Eligibility')
            ->assertDontSee('Assistance Assessment');
    }

    public function test_super_admin_can_view_sanitized_general_audit_log_metadata(): void
    {
        $actor = User::factory()->role('lswdo')->create();
        $log = AuditLog::query()->create([
            'user_id' => $actor->id,
            'action' => 'updated_case',
            'entity_type' => EclipCase::class,
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

    private function case(): EclipCase
    {
        $municipality = Municipality::query()->create(['name' => 'Restricted Case Municipality']);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => 'FR-#RESTRICTED',
            'firstname' => 'Restricted',
            'lastname' => 'Beneficiary',
            'municipality_id' => $municipality->id,
        ]);
        $creator = User::factory()->role('mblrc')->create();

        return EclipCase::query()->create([
            'case_number' => 'ECLIP-RESTRICTED',
            'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id,
            'created_by' => $creator->id,
            'status' => EclipCaseStatus::Eligible,
        ]);
    }
}
