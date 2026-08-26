<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipAssistanceCategory;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EclipAssistanceAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessor_can_save_immutable_financial_revisions(): void
    {
        [$case, $assessor] = $this->caseAndAssessor();
        $category = $this->category();

        foreach ([['1000.00', null], ['1200.00', '900.00']] as [$requested, $assessed]) {
            $this->actingAs($assessor)->post(route('eclip_assessor.assessments.store', $case), [
                'category_id' => $category->id,
                'requested_amount' => $requested,
                'assessed_amount' => $assessed,
                'justification' => 'Synthetic assessment justification.',
                'assessment_remarks' => 'Synthetic assessment remarks.',
            ])->assertRedirect();
        }

        $this->assertSame(EclipCaseStatus::AssistanceAssessment, $case->fresh()->status);
        $this->assertDatabaseCount('eclip_assistance_revisions', 2);
        $this->assertSame(
            [1, 2],
            $case->assistanceRequest->revisions()->orderBy('revision_number')->pluck('revision_number')->all(),
        );
        $this->assertDatabaseHas('eclip_status_histories', [
            'eclip_case_id' => $case->id,
            'from_status' => EclipCaseStatus::DocumentsCertified->value,
            'to_status' => EclipCaseStatus::AssistanceAssessment->value,
        ]);
    }

    public function test_completed_assessment_can_be_submitted_for_dilg_review(): void
    {
        [$case, $assessor] = $this->caseAndAssessor();
        $this->saveRevision($case, $assessor, $this->category(), '750.00');

        $this->actingAs($assessor)
            ->post(route('eclip_assessor.assessments.submit', $case))
            ->assertRedirect();

        $this->assertSame(EclipCaseStatus::SubmittedForDilgReview, $case->fresh()->status);
        $this->assertDatabaseHas('eclip_assistance_requests', [
            'eclip_case_id' => $case->id,
            'status' => 'submitted',
        ]);

        $this->actingAs($assessor)->post(route('eclip_assessor.assessments.store', $case), [
            'category_id' => $this->category('SECOND')->id,
            'requested_amount' => '1.00',
            'assessed_amount' => '1.00',
            'justification' => 'Attempted post-submission edit.',
        ])->assertForbidden();
    }

    public function test_submission_requires_an_assessed_amount(): void
    {
        [$case, $assessor] = $this->caseAndAssessor();
        $this->saveRevision($case, $assessor, $this->category(), null);

        $this->actingAs($assessor)
            ->post(route('eclip_assessor.assessments.submit', $case))
            ->assertSessionHasErrors('assessment');

        $this->assertSame(EclipCaseStatus::AssistanceAssessment, $case->fresh()->status);
    }

    public function test_assessor_cannot_access_another_municipality_case(): void
    {
        [$case] = $this->caseAndAssessor();
        $otherMunicipality = Municipality::query()->create(['name' => 'Other Assessment Municipality']);
        $outsideAssessor = User::factory()->role('eclip_assessor')->create(['municipality_id' => $otherMunicipality->id]);

        $this->actingAs($outsideAssessor)
            ->get(route('eclip_assessor.cases.show', $case))->assertForbidden();
    }

    public function test_assessment_queue_is_scoped_filterable_and_workflow_aware(): void
    {
        [$readyCase, $assessor] = $this->caseAndAssessor();
        $municipality = $readyCase->municipality;
        $category = $this->category();

        $draftCase = $this->queueCase($municipality, 'ECLIP-ASM-DRAFT', 'FR-#ASMD', EclipCaseStatus::AssistanceAssessment, $assessor);
        $draftRequest = $draftCase->assistanceRequest()->create([
            'created_by' => $assessor->id,
            'status' => 'draft',
        ]);
        $draftRequest->revisions()->create([
            'category_id' => $category->id,
            'revision_number' => 1,
            'requested_amount' => '50000.00',
            'assessed_amount' => '42500.00',
            'justification' => 'Synthetic queue assessment.',
            'created_by' => $assessor->id,
        ]);

        $reviewCase = $this->queueCase($municipality, 'ECLIP-ASM-REVIEW', 'FR-#ASMR', EclipCaseStatus::SubmittedForDilgReview, $assessor);
        $this->queueCase($municipality, 'ECLIP-ASM-APPROVED', 'FR-#ASMA', EclipCaseStatus::Approved, $assessor);

        $otherMunicipality = Municipality::query()->create(['name' => 'Outside Queue Municipality']);
        $this->queueCase($otherMunicipality, 'ECLIP-ASM-OUTSIDE', 'FR-#ASMO', EclipCaseStatus::DocumentsCertified);

        $response = $this->actingAs($assessor)->get(route('eclip_assessor.cases.index'));

        $response->assertOk()
            ->assertSee('Assistance Assessment Queue')
            ->assertSee('Start Assessment')
            ->assertSee('Continue Assessment')
            ->assertSee('View Assessment')
            ->assertSee('₱42,500.00')
            ->assertSee('Not assessed yet')
            ->assertSee('Waiting for Provincial Reviewer')
            ->assertDontSee('ECLIP-ASM-OUTSIDE');

        $this->actingAs($assessor)->get(route('eclip_assessor.cases.index', [
            'search' => $reviewCase->case_number,
            'status' => EclipCaseStatus::SubmittedForDilgReview->value,
            'sort' => 'oldest',
        ]))->assertOk()
            ->assertSee($reviewCase->case_number)
            ->assertDontSee($readyCase->case_number)
            ->assertDontSee($draftCase->case_number);
    }

    public function test_assessment_queue_rejects_unknown_status_filters(): void
    {
        [, $assessor] = $this->caseAndAssessor();

        $this->actingAs($assessor)->get(route('eclip_assessor.cases.index', [
            'status' => EclipCaseStatus::FundsTransferred->value,
        ]))->assertSessionHasErrors('status');
    }

    public function test_inactive_category_cannot_be_used_or_submitted(): void
    {
        [$case, $assessor] = $this->caseAndAssessor();
        $category = $this->category();
        $category->update(['is_active' => false]);

        $this->actingAs($assessor)->post(route('eclip_assessor.assessments.store', $case), [
            'category_id' => $category->id,
            'requested_amount' => '500.00',
            'assessed_amount' => '400.00',
            'justification' => 'Synthetic inactive category test.',
        ])->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('eclip_assistance_revisions', 0);
    }

    public function test_katuparan_admin_configures_categories_with_audit_history(): void
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)->post(route('admin.eclip.assistance-categories.store'), [
            'code' => 'synthetic_category',
            'name' => 'Synthetic Category',
            'description' => 'Test-only category.',
            'sort_order' => 1,
        ])->assertRedirect();

        $category = EclipAssistanceCategory::query()->firstOrFail();
        $this->assertSame('SYNTHETIC_CATEGORY', $category->code);
        $this->assertDatabaseHas('eclip_assistance_category_histories', [
            'category_id' => $category->id,
            'user_id' => $admin->id,
            'action' => 'created',
        ]);
    }

    private function caseAndAssessor(): array
    {
        $municipality = Municipality::query()->create(['name' => 'Assessment Municipality']);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => 'FR-#ASM1', 'firstname' => 'Synthetic', 'lastname' => 'Assessment',
            'municipality_id' => $municipality->id,
        ]);
        $creator = User::factory()->role('mblrc')->create();
        $assessor = User::factory()->role('eclip_assessor')->create(['municipality_id' => $municipality->id]);
        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-ASM-000001', 'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id, 'created_by' => $creator->id,
            'status' => EclipCaseStatus::DocumentsCertified,
        ]);
        $case->participantAssignments()->create([
            'user_id' => $assessor->id,
            'participant_role' => 'case_processor',
            'assigned_by' => $creator->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);

        return [$case, $assessor];
    }

    private function category(string $code = 'SYNTHETIC'): EclipAssistanceCategory
    {
        return EclipAssistanceCategory::query()->create([
            'code' => $code, 'name' => "{$code} Category", 'is_active' => true,
        ]);
    }

    private function queueCase(
        Municipality $municipality,
        string $caseNumber,
        string $classifiedId,
        EclipCaseStatus $status,
        ?User $assessor = null,
    ): EclipCase {
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => $classifiedId,
            'firstname' => 'Synthetic',
            'lastname' => 'Queue',
            'municipality_id' => $municipality->id,
        ]);

        $case = EclipCase::query()->create([
            'case_number' => $caseNumber,
            'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id,
            'created_by' => User::factory()->role('mblrc')->create()->id,
            'status' => $status,
        ]);

        if ($assessor) {
            $case->participantAssignments()->create([
                'user_id' => $assessor->id,
                'participant_role' => 'case_processor',
                'assigned_by' => $case->created_by,
                'assigned_at' => now(),
                'is_active' => true,
            ]);
        }

        return $case;
    }

    private function saveRevision(EclipCase $case, User $assessor, EclipAssistanceCategory $category, ?string $assessed): void
    {
        $this->actingAs($assessor)->post(route('eclip_assessor.assessments.store', $case), [
            'category_id' => $category->id,
            'requested_amount' => '1000.00',
            'assessed_amount' => $assessed,
            'justification' => 'Synthetic complete justification.',
        ])->assertRedirect();
    }
}
