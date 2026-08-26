<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EclipFinancialMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_provincial_focal_records_and_corrects_liquidation_with_immutable_history(): void
    {
        [$case, $provincial] = $this->financialCase('8A', 'dilg_provincial_focal');

        $this->actingAs($provincial)->get(route('dilg_reviewer.financial-monitoring.show', $case))
            ->assertOk()
            ->assertSee('Liquidation Requirements');

        $this->actingAs($provincial)->post(route('dilg_reviewer.financial-monitoring.liquidations.store', $case), [
            'assistance_category' => 'Immediate Assistance',
            'requirement_name' => 'Signed acknowledgment',
            'status' => 'returned',
            'returned_at' => today()->format('Y-m-d'),
            'return_reason' => 'Signature page is incomplete.',
        ])->assertRedirect();

        $requirement = $case->liquidationRequirements()->firstOrFail();
        $this->actingAs($provincial)->put(route('dilg_reviewer.financial-monitoring.liquidations.update', $requirement), [
            'assistance_category' => $requirement->assistance_category,
            'requirement_name' => $requirement->requirement_name,
            'status' => 'accepted',
            'accepted_at' => today()->format('Y-m-d'),
            'resubmitted_at' => today()->format('Y-m-d'),
            'return_reason' => $requirement->return_reason,
        ])->assertRedirect();

        $this->assertSame('accepted', $requirement->fresh()->status);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $case->workflowActivities()->where('step_code', '8A')->value('id'),
            'event' => 'liquidation_requirement_updated',
            'actor_role' => 'dilg_provincial_focal',
        ]);
        $this->assertDatabaseCount('audit_logs', 2);
    }

    public function test_regional_focal_records_return_and_acceptance_history(): void
    {
        [$case, $regional] = $this->financialCase('9', 'dilg_regional');

        $this->actingAs($regional)->post(route('dilg_reviewer.financial-monitoring.disbursement-reports.store', $case), [
            'reporting_month' => '2026-08',
            'form_11_reference' => 'FORM11-2026-08',
            'status' => 'returned',
            'returned_at' => '2026-08-05',
            'return_reason' => 'Recipient total needs correction.',
        ])->assertRedirect();

        $report = $case->regionalDisbursementReports()->firstOrFail();
        $this->actingAs($regional)->put(route('dilg_reviewer.financial-monitoring.disbursement-reports.update', $report), [
            'reporting_month' => '2026-08',
            'form_11_reference' => 'FORM11-2026-08-R1',
            'status' => 'accepted',
            'accepted_at' => '2026-08-07',
            'resubmitted_at' => '2026-08-06',
            'return_reason' => $report->return_reason,
        ])->assertRedirect();

        $this->assertSame('accepted', $report->fresh()->status);
        $this->assertDatabaseHas('eclip_workflow_activity_histories', [
            'activity_id' => $case->workflowActivities()->where('step_code', '9')->value('id'),
            'event' => 'regional_report_updated',
            'actor_role' => 'dilg_regional',
        ]);
    }

    public function test_financial_monitoring_rejects_an_out_of_scope_office(): void
    {
        [$case] = $this->financialCase('8A', 'dilg_provincial_focal');
        $outsideMunicipality = Municipality::query()->create(['name' => 'Outside Financial Municipality']);
        $outside = User::factory()->role('dilg_provincial_focal')->create(['municipality_id' => $outsideMunicipality->id]);

        $this->actingAs($outside)->post(route('dilg_reviewer.financial-monitoring.liquidations.store', $case), [
            'assistance_category' => 'Test',
            'requirement_name' => 'Test requirement',
            'status' => 'missing',
        ])->assertForbidden();
    }

    private function financialCase(string $stepCode, string $role): array
    {
        $municipality = Municipality::query()->create(['name' => 'Financial Monitoring Municipality']);
        $mblrc = User::factory()->role('mblrc')->create();
        $actor = User::factory()->role($role)->create([
            'municipality_id' => $role === 'dilg_provincial_focal' ? $municipality->id : null,
        ]);
        $beneficiary = FormerRebel::query()->create([
            'classified_id' => 'FR-#FIN1',
            'firstname' => 'Financial',
            'lastname' => 'Beneficiary',
            'municipality_id' => $municipality->id,
        ]);
        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-FIN-000001',
            'former_rebel_id' => $beneficiary->id,
            'municipality_id' => $municipality->id,
            'created_by' => $mblrc->id,
            'status' => EclipCaseStatus::Approved,
        ]);
        $case->workflowActivities()->create([
            'step_code' => $stepCode,
            'phase' => 3,
            'title' => $stepCode === '8A' ? 'Liquidation Requirements' : 'Regional Disbursement Report',
            'status' => 'pending',
            'responsible_roles' => [$role],
            'required_documents' => [],
            'available_at' => now(),
            'data' => [],
        ]);

        return [$case, $actor];
    }
}
