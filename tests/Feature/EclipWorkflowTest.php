<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\Barangay;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EclipWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mblrc_can_create_and_submit_a_case_for_eligibility_review(): void
    {
        [$formerRebel] = $this->beneficiary();
        $mblrc = User::factory()->role('mblrc')->create();

        $this->actingAs($mblrc)->post(route('mblrc.eclip.store'), [
            'former_rebel_id' => $formerRebel->id,
        ])->assertRedirect();

        $case = EclipCase::query()->firstOrFail();
        $this->assertSame(EclipCaseStatus::Draft, $case->status);
        $this->assertNotNull($case->case_number);

        $this->actingAs($mblrc)
            ->post(route('mblrc.eclip.submit', $case))
            ->assertRedirect(route('mblrc.eclip.show', $case));

        $this->assertSame(EclipCaseStatus::SubmittedForEligibility, $case->fresh()->status);
        $this->assertDatabaseHas('eclip_status_histories', [
            'eclip_case_id' => $case->id,
            'from_status' => EclipCaseStatus::Draft->value,
            'to_status' => EclipCaseStatus::SubmittedForEligibility->value,
            'user_id' => $mblrc->id,
        ]);
    }

    public function test_duplicate_active_case_is_rejected(): void
    {
        [$formerRebel] = $this->beneficiary();
        $mblrc = User::factory()->role('mblrc')->create();

        $payload = ['former_rebel_id' => $formerRebel->id];
        $this->actingAs($mblrc)->post(route('mblrc.eclip.store'), $payload)->assertRedirect();
        $this->actingAs($mblrc)->post(route('mblrc.eclip.store'), $payload)->assertSessionHasErrors('former_rebel_id');

        $this->assertDatabaseCount('eclip_cases', 1);
    }

    public function test_lswdo_can_only_view_cases_in_their_municipality(): void
    {
        [$formerRebel, $municipality] = $this->beneficiary();
        $case = $this->caseFor($formerRebel, EclipCaseStatus::SubmittedForEligibility);
        $otherMunicipality = Municipality::query()->create(['name' => 'Other Municipality']);
        $outsideLswdo = User::factory()->role('lswdo')->create(['municipality_id' => $otherMunicipality->id]);
        $localLswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);

        $this->actingAs($outsideLswdo)->get(route('lswdo.eclip.show', $case))->assertForbidden();
        $this->actingAs($localLswdo)->get(route('lswdo.eclip.show', $case))->assertOk();
    }

    public function test_lswdo_eligibility_decision_is_recorded_with_history(): void
    {
        [$formerRebel, $municipality] = $this->beneficiary();
        $case = $this->caseFor($formerRebel, EclipCaseStatus::SubmittedForEligibility);
        $lswdo = User::factory()->role('lswdo')->create(['municipality_id' => $municipality->id]);

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

        return EclipCase::query()->create([
            'case_number' => 'ECLIP-TEST-000001',
            'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $formerRebel->municipality_id,
            'created_by' => $creator->id,
            'status' => $status,
            'submitted_at' => now(),
        ]);
    }
}
