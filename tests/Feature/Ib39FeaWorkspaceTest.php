<?php

namespace Tests\Feature;

use App\Enums\Ib39FrCategory;
use App\Models\Barangay;
use App\Models\Municipality;
use App\Models\User;
use App\Services\Ib39SurfacedFormerRebelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Ib39FeaWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_and_workspace_are_limited_to_active_39th_ib_users(): void
    {
        $processing = $this->processing();
        $routes = [route('ib39.fea.index'), route('ib39.fea.show', $processing)];

        foreach ($routes as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }

        $inactive = User::factory()->role('39th_ib')->create(['is_active' => false]);
        foreach ($routes as $url) {
            $this->actingAs($inactive)->get($url)->assertRedirect(route('login'));
        }

        foreach ($this->otherRoles() as $role) {
            $user = User::factory()->role($role)->create();
            foreach ($routes as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }

        $actor = User::factory()->role('39th_ib')->create();
        foreach ($routes as $url) {
            $this->actingAs($actor)->get($url)->assertOk();
        }
    }

    public function test_queue_displays_approved_columns_status_and_navigation_entry(): void
    {
        $processing = $this->processing();
        $actor = User::factory()->role('39th_ib')->create();

        $this->actingAs($actor)->get(route('ib39.fea.index'))
            ->assertOk()
            ->assertSee('FEA Processing')
            ->assertSee('FR Reference')
            ->assertSee('PSWDO Access Status')
            ->assertSee('Awaiting PSWDO Enrollment')
            ->assertSee('View FEA Record')
            ->assertSee(route('ib39.fea.show', $processing));
    }

    public function test_workspace_lists_exactly_six_approved_requirements_and_private_draft_controls(): void
    {
        $processing = $this->processing();
        $actor = User::factory()->role('39th_ib')->create();
        $response = $this->actingAs($actor)->get(route('ib39.fea.show', $processing))->assertOk();

        $response->assertSee('No securely linked PSWDO enrollment is available. Final FEA processing is disabled.')
            ->assertSee('Awaiting PSWDO Enrollment')
            ->assertSee('Technical Inspection Report')
            ->assertSee('Cost Valuation of Inventoried Firearms')
            ->assertSee('Property Turn-In Slip')
            ->assertSee('Justification on the TIR and CVC/CVIF')
            ->assertSee('Photograph of the firearm')
            ->assertSee('Photograph of the FR with the firearm')
            ->assertSee('Final actions unavailable')
            ->assertSee('Private Draft Upload — DRAFT — NOT FINAL')
            ->assertSee('type="file"', false);
    }

    public function test_soft_deleted_parent_is_absent_from_queue_and_workspace_is_denied(): void
    {
        $processing = $this->processing();
        $processing->surfacedFormerRebel->delete();
        $actor = User::factory()->role('39th_ib')->create();

        $this->actingAs($actor)->get(route('ib39.fea.index'))
            ->assertOk()
            ->assertDontSee($processing->surfacedFormerRebel->reference_number);
        $this->actingAs($actor)->get(route('ib39.fea.show', $processing))->assertForbidden();
    }

    private function processing()
    {
        $municipality = Municipality::query()->create(['name' => 'FEA Municipality']);
        $barangay = Barangay::query()->create(['municipality_id' => $municipality->id, 'name' => 'FEA Barangay']);
        $actor = User::factory()->role('39th_ib')->create();
        $record = app(Ib39SurfacedFormerRebelService::class)->create([
            'first_name' => 'Synthetic',
            'last_name' => 'Workspace',
            'category' => Ib39FrCategory::RegularMember->value,
            'municipality_id' => $municipality->id,
            'barangay_id' => $barangay->id,
            'surfaced_at' => '2026-08-30',
            'possessed_firearms' => true,
        ], $actor);

        return $record->feaProcessing()->firstOrFail();
    }

    private function otherRoles(): array
    {
        return [
            'super_admin', 'admin', 'lgu', 'gov_agency', 'mblrc', 'lswdo', 'japic',
            'dilg_provincial_focal', 'dilg_regional', 'nboo_eclip_pmo', 'dilg_fms',
            'local_eclip_committee', 'pnp', 'afp', 'eclip_assessor', 'dilg_reviewer',
            'eclip_funding_officer',
        ];
    }
}
