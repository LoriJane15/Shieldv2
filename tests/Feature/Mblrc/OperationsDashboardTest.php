<?php

namespace Tests\Feature\Mblrc;

use App\Enums\EclipCaseStatus;
use App\Models\Barangay;
use App\Models\EclipCase;
use App\Models\EclipDocumentRequirement;
use App\Models\FormerRebel;
use App\Models\FrProgramStatus;
use App\Models\MblrcEnrollment;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_real_operational_counts_and_supported_actions(): void
    {
        $user = User::factory()->role('mblrc')->create();
        [$municipality, $barangay] = $this->location();
        $active = $this->formerRebel('FR-#OPS-001', $municipality, $barangay, [
            'status' => 'Active',
            'registered_at' => now(),
            'latitude' => null,
            'longitude' => null,
        ]);
        $reintegrated = $this->formerRebel('FR-#OPS-002', $municipality, $barangay, [
            'status' => 'Reintegrated',
            'registered_at' => now()->subMonthNoOverflow(),
            'latitude' => 6.75,
            'longitude' => 125.35,
        ]);
        FrProgramStatus::query()->create([
            'former_rebel_id' => $active->id,
            'reintegration_status' => 'On-going',
        ]);
        FrProgramStatus::query()->create([
            'former_rebel_id' => $reintegrated->id,
            'reintegration_status' => 'Completed',
        ]);
        MblrcEnrollment::query()->create([
            'former_rebel_id' => $active->id,
            'assigned_user_id' => $user->id,
            'created_by' => $user->id,
            'status' => 'in_progress',
            'integration_started_at' => now()->subMonthsNoOverflow(5),
        ]);
        EclipDocumentRequirement::query()->create([
            'code' => 'OPS-REQ',
            'name' => 'Operations Requirement',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        EclipCase::query()->create([
            'case_number' => 'ECLIP-OPS-001',
            'former_rebel_id' => $active->id,
            'municipality_id' => $municipality->id,
            'created_by' => $user->id,
            'status' => EclipCaseStatus::Draft,
        ]);

        $response = $this->actingAs($user)->get(route('mblrc.dashboard'));

        $response->assertOk()
            ->assertSee('Operations command center')
            ->assertDontSee('System Active')
            ->assertSee('mdi-account-group-outline', false)
            ->assertDontSee('Quick Actions')
            ->assertSee('Needs Attention')
            ->assertSee('Recent Activity')
            ->assertSee('Geographic Monitoring Map')
            ->assertSee('Expand Map')
            ->assertSee('mapExpandModal', false)
            ->assertSee('data-map-modal-host', false)
            ->assertSee('map.invalidateSize({ pan: false })', false)
            ->assertSee("mapModal.addEventListener('shown.bs.modal'", false)
            ->assertSee('Reintegration trends at a glance')
            ->assertSee('Monthly Program Movement')
            ->assertSee('Registry Outcome Trend')
            ->assertSee('analytics-metric-value', false)
            ->assertSee('Cases missing required documents')
            ->assertSee('Profiles without geotags')
            ->assertDontSee('Live operational data')
            ->assertSee('mdi-check-decagram', false)
            ->assertSee('mdi-chart-line-variant', false)
            ->assertSee('mdi-map-marker-radius', false)
            ->assertSee('prefers-reduced-motion: reduce', false)
            ->assertSee('duration: 950', false)
            ->assertSee('animateMetric', false)
            ->assertSee("classList.add('analytics-loading')", false)
            ->assertSee("classList.add('analytics-loaded')", false)
            ->assertViewHas('kpis', fn ($kpis) => collect($kpis)->pluck('value', 'label')->all() === [
                'Registered FRs' => 2,
                'Active' => 1,
                'Reintegrated' => 1,
                'Ongoing' => 1,
                'At Risk' => 1,
            ])
            ->assertViewHas('attentionItems', function ($items) {
                $counts = collect($items)->pluck('count', 'title');

                return $counts['Integration monitoring overdue'] === 1
                    && $counts['Cases missing required documents'] === 1
                    && $counts['Profiles without geotags'] === 1;
            });
    }

    public function test_analytics_and_map_payloads_support_operational_widgets_without_names(): void
    {
        $user = User::factory()->role('mblrc')->create();
        [$municipality, $barangay] = $this->location();
        $profile = $this->formerRebel('FR-#MAP-001', $municipality, $barangay, [
            'status' => 'Active',
            'registered_at' => now(),
            'latitude' => 6.75,
            'longitude' => 125.35,
        ]);
        FrProgramStatus::query()->create([
            'former_rebel_id' => $profile->id,
            'reintegration_status' => 'On-going',
        ]);

        $this->actingAs($user)->getJson(route('mblrc.analytics'))
            ->assertOk()
            ->assertJsonStructure([
                'labels',
                'program' => ['not_started', 'ongoing', 'completed'],
                'overall' => ['registered', 'active', 'reintegrated', 'completion_rate'],
            ]);

        $this->actingAs($user)->getJson(route('mblrc.fr.locations'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonFragment([
                'classified_id' => 'FR-#MAP-001',
                'municipality' => 'Operations Municipality',
                'program_status' => 'On-going',
            ])
            ->assertJsonMissingPath('0.firstname')
            ->assertJsonMissingPath('0.lastname')
            ->assertJsonMissingPath('0.name');
    }

    private function location(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Operations Municipality']);
        $barangay = Barangay::query()->create([
            'municipality_id' => $municipality->id,
            'name' => 'Operations Barangay',
        ]);

        return [$municipality, $barangay];
    }

    private function formerRebel(string $classifiedId, Municipality $municipality, Barangay $barangay, array $attributes): FormerRebel
    {
        return FormerRebel::query()->create(array_merge([
            'classified_id' => $classifiedId,
            'firstname' => 'Sensitive',
            'lastname' => 'Beneficiary',
            'municipality_id' => $municipality->id,
            'barangay_id' => $barangay->id,
        ], $attributes));
    }
}
