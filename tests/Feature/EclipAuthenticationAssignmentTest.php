<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use App\Services\EclipAuthenticationService;
use App\Services\EclipCaseWorkflowService;
use App\Services\EclipOfficialWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EclipAuthenticationAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_assigned_japic_reviewer_can_authenticate_by_explicit_decision(): void
    {
        [$case, $lswdo] = $this->eligibleAssignedCase();
        $assignedJapic = User::factory()->role('japic')->create();
        $otherJapic = User::factory()->role('japic')->create();
        $service = app(EclipAuthenticationService::class);

        $authentication = $service->request($case, $assignedJapic, $lswdo, '127.0.0.1');

        $this->assertSame(EclipCaseStatus::AuthenticationPending, $case->fresh()->status);
        $this->assertDatabaseHas('eclip_case_participants', [
            'eclip_case_id' => $case->id,
            'user_id' => $assignedJapic->id,
            'participant_role' => 'authentication_reviewer',
            'is_active' => true,
        ]);

        try {
            $service->start($authentication, $otherJapic, null);
            $this->fail('An unassigned JAPIC user was allowed to start review.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $service->start($authentication, $assignedJapic, '127.0.0.1');
        $service->decide($authentication->fresh(), $assignedJapic, 'authenticated', 'JAPIC-CERT-001', null, '127.0.0.1');

        $this->assertSame(EclipCaseStatus::Authenticated, $case->fresh()->status);
        $this->assertDatabaseHas('eclip_authentication_requests', [
            'id' => $authentication->id,
            'status' => 'authenticated',
            'certification_reference' => 'JAPIC-CERT-001',
        ]);
        $this->assertDatabaseHas('eclip_workflow_activities', [
            'eclip_case_id' => $case->id,
            'step_code' => '4A',
            'status' => 'completed',
        ]);
    }

    public function test_certificate_reference_without_explicit_decision_does_not_authenticate(): void
    {
        [$case, $lswdo] = $this->eligibleAssignedCase();
        $japic = User::factory()->role('japic')->create();
        $authentication = app(EclipAuthenticationService::class)->request($case, $japic, $lswdo, null);

        $authentication->update(['certification_reference' => 'UPLOADED-ONLY']);

        $this->assertSame(EclipCaseStatus::AuthenticationPending, $case->fresh()->status);
        $this->assertSame('pending', $authentication->fresh()->status);
    }

    private function eligibleAssignedCase(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Authentication Municipality']);
        $mblrc = User::factory()->role('mblrc')->create();
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $beneficiary = FormerRebel::query()->create(['classified_id' => 'FR-AUTH-'.uniqid(), 'firstname' => 'Auth', 'lastname' => 'Subject']);
        $case = EclipCase::query()->create([
            'case_number' => 'CASE-AUTH-'.uniqid(),
            'former_rebel_id' => $beneficiary->id,
            'municipality_id' => $municipality->id,
            'created_by' => $mblrc->id,
            'assigned_to' => $lswdo->id,
            'status' => EclipCaseStatus::SubmittedForEligibility,
        ]);
        $case->participantAssignments()->create([
            'user_id' => $lswdo->id, 'participant_role' => 'case_processor',
            'assigned_by' => $mblrc->id, 'assigned_at' => now(), 'is_active' => true,
        ]);
        app(EclipOfficialWorkflowService::class)->initialize($case, $lswdo, null, [
            'intention_to_surface' => ['source' => 'Enrollment', 'source_record' => 'INT-1'],
            'receiving_unit_coordination' => ['source' => 'Coordination', 'source_record' => 'COORD-1'],
        ]);
        app(EclipCaseWorkflowService::class)->decideEligibility($case, $lswdo, 'eligible', null, null);

        return [$case->fresh(), $lswdo];
    }
}
