<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EclipInterventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_lswdo_can_record_multiple_social_protection_entries_with_history(): void
    {
        [$case, $lswdo] = $this->caseWithProcessor();
        foreach (['Counseling', 'Temporary shelter'] as $title) {
            $this->actingAs($lswdo)->post(route('lswdo.eclip.interventions.store', $case), [
                'stage' => 'social_protection', 'title' => $title, 'provider' => 'LSWDO', 'status' => 'completed',
                'outcome' => 'Service delivered and acknowledged.',
            ])->assertRedirect();
        }

        $this->assertDatabaseCount('eclip_interventions', 2);
        $this->assertDatabaseCount('eclip_intervention_histories', 2);
    }

    public function test_reintegration_intervention_requires_a_final_outcome_before_case_closure(): void
    {
        [$case, $lswdo] = $this->caseWithProcessor();
        $this->actingAs($lswdo)->post(route('lswdo.eclip.interventions.store', $case), [
            'stage' => 'reintegration', 'title' => 'Livelihood starter support',
            'provider' => 'Partner Agency', 'status' => 'in_progress',
        ])->assertRedirect();
        $intervention = $case->interventions()->firstOrFail();
        $closure = $case->workflowActivities()->create([
            'step_code' => '14', 'phase' => 4, 'title' => 'Close Remaining Assistance',
            'status' => 'pending', 'responsible_roles' => ['lswdo'], 'available_at' => now(),
        ]);

        $this->actingAs($lswdo)
            ->patch(route('eclip.workflow-activities.update', $closure), ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->actingAs($lswdo)->put(route('lswdo.eclip.interventions.update', $intervention), [
            'stage' => 'reintegration', 'title' => 'Livelihood starter support',
            'provider' => 'Partner Agency', 'status' => 'completed',
            'outcome' => 'Assistance delivered and acknowledged.',
        ])->assertRedirect();

        $this->actingAs($lswdo)
            ->patch(route('eclip.workflow-activities.update', $closure), ['status' => 'completed'])
            ->assertRedirect();

        $this->assertSame('completed', $closure->fresh()->status);
        $this->assertDatabaseHas('eclip_intervention_histories', [
            'intervention_id' => $intervention->id, 'action' => 'updated',
        ]);
    }

    private function caseWithProcessor(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Intervention Test Municipality']);
        $formerRebel = FormerRebel::query()->create(['classified_id' => 'FR-INTERVENTION-001', 'firstname' => 'Synthetic', 'lastname' => 'Service', 'municipality_id' => $municipality->id]);
        $mblrc = User::factory()->role('mblrc')->create();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $case = EclipCase::query()->create(['case_number' => 'ECLIP-INTERVENTION-000001', 'former_rebel_id' => $formerRebel->id, 'municipality_id' => $municipality->id, 'created_by' => $mblrc->id, 'status' => EclipCaseStatus::Eligible]);
        $case->participantAssignments()->create(['user_id' => $lswdo->id, 'participant_role' => 'case_processor', 'assigned_by' => $mblrc->id, 'assigned_at' => now(), 'is_active' => true]);

        return [$case, $lswdo];
    }
}
