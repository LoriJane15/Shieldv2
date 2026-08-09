<?php

namespace Tests\Feature;

use App\Models\FormerRebel;
use App\Models\MblrcEnrollment;
use App\Models\Municipality;
use App\Models\User;
use App\Services\EclipOfficialWorkflowService;
use App\Services\MblrcReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EclipIntakeAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_completion_creates_one_explicit_referral_and_acceptance_is_idempotent(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Assigned Municipality']);
        $mblrc = User::factory()->role('mblrc')->create();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $beneficiary = FormerRebel::query()->create([
            'classified_id' => 'FR-#9001',
            'firstname' => 'Classified',
            'lastname' => 'Beneficiary',
        ]);
        $enrollment = MblrcEnrollment::query()->create([
            'former_rebel_id' => $beneficiary->id,
            'assigned_user_id' => $mblrc->id,
            'created_by' => $mblrc->id,
            'status' => 'in_progress',
            'integration_started_at' => '2026-01-01',
        ]);
        $data = [
            'integration_completed_at' => '2026-04-01',
            'verified_municipality_id' => $municipality->id,
            'phase_one_evidence' => [
                'intention_to_surface' => ['source' => 'MBLRC enrollment', 'source_record' => 'INT-9001'],
                'receiving_unit_coordination' => ['source' => 'Coordination log', 'source_record' => 'COORD-9001'],
            ],
        ];
        $service = app(MblrcReferralService::class);

        $firstReferral = $service->completeIntegration($enrollment, $data, $mblrc);
        $secondReferral = $service->completeIntegration($enrollment->fresh(), $data, $mblrc);

        $this->assertTrue($firstReferral->is($secondReferral));
        $this->assertSame($lswdo->id, $firstReferral->assigned_to);
        $this->assertDatabaseCount('lswdo_referrals', 1);

        $firstCase = $service->accept($firstReferral, $lswdo, app(EclipOfficialWorkflowService::class), '127.0.0.1');
        $secondCase = $service->accept($firstReferral->fresh(), $lswdo, app(EclipOfficialWorkflowService::class), '127.0.0.1');

        $this->assertTrue($firstCase->is($secondCase));
        $this->assertDatabaseCount('eclip_cases', 1);
        $this->assertDatabaseHas('eclip_case_participants', [
            'eclip_case_id' => $firstCase->id,
            'user_id' => $lswdo->id,
            'participant_role' => 'case_processor',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('eclip_workflow_activities', [
            'eclip_case_id' => $firstCase->id,
            'step_code' => '1',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('eclip_workflow_activities', [
            'eclip_case_id' => $firstCase->id,
            'step_code' => '2',
            'status' => 'completed',
        ]);
    }

    public function test_unassigned_or_differently_assigned_referral_cannot_be_accepted(): void
    {
        $municipality = Municipality::query()->create(['name' => 'Scoped Municipality']);
        $mblrc = User::factory()->role('mblrc')->create();
        User::factory()->role('lswdo')->count(2)->create(['municipality_id' => $municipality->id]);
        $outsider = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $beneficiary = FormerRebel::query()->create(['classified_id' => 'FR-#9002', 'firstname' => 'Test', 'lastname' => 'Person']);
        $enrollment = MblrcEnrollment::query()->create([
            'former_rebel_id' => $beneficiary->id,
            'assigned_user_id' => $mblrc->id,
            'created_by' => $mblrc->id,
            'integration_started_at' => '2026-01-01',
        ]);
        $service = app(MblrcReferralService::class);
        $referral = $service->completeIntegration($enrollment, [
            'integration_completed_at' => '2026-04-01',
            'verified_municipality_id' => $municipality->id,
            'phase_one_evidence' => [
                'intention_to_surface' => ['source' => 'Source', 'source_record' => 'A'],
                'receiving_unit_coordination' => ['source' => 'Source', 'source_record' => 'B'],
            ],
        ], $mblrc);

        $this->assertNull($referral->assigned_to);
        $this->expectException(HttpException::class);
        $service->accept($referral, $outsider, app(EclipOfficialWorkflowService::class), null);
    }
}
