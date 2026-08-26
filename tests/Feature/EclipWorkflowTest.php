<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\Barangay;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use App\Services\EclipOfficialWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EclipWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mblrc_cannot_bypass_the_verified_referral_intake(): void
    {
        $mblrc = User::factory()->role('mblrc')->create();

        $this->assertFalse(Route::has('mblrc.eclip.store'));
        $this->assertFalse($mblrc->can('create', EclipCase::class));
    }

    public function test_unassigned_mblrc_user_cannot_submit_an_existing_draft(): void
    {
        [$formerRebel] = $this->beneficiary();
        $mblrc = User::factory()->role('mblrc')->create();
        $case = $this->caseFor($formerRebel, EclipCaseStatus::Draft);

        $this->actingAs($mblrc)
            ->post(route('mblrc.eclip.submit', $case))
            ->assertForbidden();
    }

    public function test_lswdo_can_only_view_cases_in_their_municipality(): void
    {
        [$formerRebel, $municipality] = $this->beneficiary();
        $case = $this->caseFor($formerRebel, EclipCaseStatus::SubmittedForEligibility);
        $otherMunicipality = Municipality::query()->create(['name' => 'Other Municipality']);
        $outsideLswdo = User::factory()->role('lswdo')->create(['municipality_id' => $otherMunicipality->id]);
        $localLswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $this->assignProcessor($case, $localLswdo);

        $this->actingAs($outsideLswdo)->get(route('lswdo.eclip.show', $case))->assertForbidden();
        $this->actingAs($localLswdo)->get(route('lswdo.eclip.show', $case))->assertOk();
    }

    public function test_lswdo_eligibility_decision_is_recorded_with_history(): void
    {
        [$formerRebel, $municipality] = $this->beneficiary();
        $case = $this->caseFor($formerRebel, EclipCaseStatus::SubmittedForEligibility);
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $this->assignProcessor($case, $lswdo);

        $this->actingAs($lswdo)->post(route('lswdo.eclip.eligibility.decide', $case), [
            'decision' => 'eligible',
            'remarks' => null,
        ])->assertRedirect(route('lswdo.eclip.show', $case));

        $this->assertSame(EclipCaseStatus::Eligible, $case->fresh()->status);
        $this->assertDatabaseHas('eclip_eligibility_reviews', [
            'eclip_case_id' => $case->id,
            'reviewed_by' => $lswdo->id,
            'decision' => 'eligible',
        ]);
        $this->assertDatabaseHas('eclip_status_histories', [
            'eclip_case_id' => $case->id,
            'to_status' => EclipCaseStatus::Eligible->value,
            'user_id' => $lswdo->id,
        ]);
    }

    public function test_return_and_ineligible_decisions_require_remarks(): void
    {
        [$formerRebel, $municipality] = $this->beneficiary();
        $case = $this->caseFor($formerRebel, EclipCaseStatus::SubmittedForEligibility);
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);
        $this->assignProcessor($case, $lswdo);

        foreach (['returned', 'ineligible'] as $decision) {
            $this->actingAs($lswdo)->post(route('lswdo.eclip.eligibility.decide', $case), [
                'decision' => $decision,
                'remarks' => '',
            ])->assertSessionHasErrors('remarks');
        }

        $this->assertSame(EclipCaseStatus::SubmittedForEligibility, $case->fresh()->status);
        $this->assertDatabaseCount('eclip_eligibility_reviews', 0);
    }

    public function test_non_eclip_role_cannot_submit_or_review_cases(): void
    {
        [$formerRebel] = $this->beneficiary();
        $case = $this->caseFor($formerRebel, EclipCaseStatus::SubmittedForEligibility);
        $lgu = User::factory()->role('lgu')->create(['municipality_id' => $formerRebel->municipality_id]);

        $this->actingAs($lgu)->post(route('mblrc.eclip.submit', $case))->assertForbidden();
        $this->actingAs($lgu)->post(route('lswdo.eclip.eligibility.decide', $case), [
            'decision' => 'eligible',
        ])->assertForbidden();
    }

    private function beneficiary(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Scoped Municipality']);
        $barangay = Barangay::query()->create(['municipality_id' => $municipality->id, 'name' => 'Test Barangay']);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => 'FR-#0001',
            'firstname' => 'Synthetic',
            'lastname' => 'Beneficiary',
            'municipality_id' => $municipality->id,
            'barangay_id' => $barangay->id,
        ]);

        return [$formerRebel, $municipality];
    }

    private function caseFor(FormerRebel $formerRebel, EclipCaseStatus $status): EclipCase
    {
        $creator = User::factory()->role('mblrc')->create();

        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-TEST-000001',
            'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $formerRebel->municipality_id,
            'created_by' => $creator->id,
            'status' => $status,
            'submitted_at' => now(),
        ]);

        if ($status === EclipCaseStatus::SubmittedForEligibility) {
            app(EclipOfficialWorkflowService::class)->initialize($case, null, null, [
                'intention_to_surface' => ['source' => 'Test intake', 'source_record' => 'INT-001'],
                'receiving_unit_coordination' => ['source' => 'Test intake', 'source_record' => 'COORD-001'],
            ]);
        }

        return $case;
    }

    private function assignProcessor(EclipCase $case, User $user): void
    {
        $case->participantAssignments()->create([
            'user_id' => $user->id,
            'participant_role' => 'case_processor',
            'assigned_by' => $case->created_by,
            'assigned_at' => now(),
            'is_active' => true,
        ]);
    }
}
