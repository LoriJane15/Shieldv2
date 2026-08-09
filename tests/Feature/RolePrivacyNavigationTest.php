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
            'mblrc' => ['FR/FVE Registry', 'Integration Monitoring'],
            'lswdo' => ['MBLRC Referrals', 'E-CLIP Cases'],
        ];

        foreach ($expectations as $role => $labels) {
            $user = User::factory()->role($role)->create();
            $response = $this->actingAs($user)->get(route($user->homeRoute()))->assertOk();

            foreach ($labels as $label) {
                $response->assertSee($label);
            }
        }
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
