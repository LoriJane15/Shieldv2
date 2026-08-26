<?php

namespace Tests\Feature;

use App\Enums\EclipCaseStatus;
use App\Models\EclipAssistanceCategory;
use App\Models\EclipAssistanceRequest;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\Municipality;
use App\Models\User;
use App\Notifications\EclipCaseActionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EclipDilgReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dilg_reviewer_can_approve_the_submitted_revision(): void
    {
        [$case, $assessor, $reviewer, $request, $revision] = $this->submittedCase();

        $this->actingAs($reviewer)->post(route('dilg_reviewer.cases.decide', $case), [
            'decision' => 'approved', 'feedback' => null,
            'form_10_reference' => 'FORM10-LEGACY-001',
            'endorsement_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame(EclipCaseStatus::Approved, $case->fresh()->status);
        $this->assertSame('approved', $request->fresh()->status);
        $this->assertDatabaseHas('eclip_dilg_reviews', [
            'eclip_case_id' => $case->id,
            'assistance_revision_id' => $revision->id,
            'reviewed_by' => $reviewer->id,
            'decision' => 'approved',
        ]);
        $this->assertCount(1, $assessor->notifications()->get());
    }

    public function test_official_dilg_roles_must_complete_each_review_level_in_order(): void
    {
        [$case, $lswdo, , $request, $revision] = $this->submittedCase();
        $lswdo->update(['role' => 'lswdo']);
        $provincial = User::factory()->role('dilg_provincial_focal')->create(['municipality_id' => $case->municipality_id]);
        $regional = User::factory()->role('dilg_regional')->create();
        $national = User::factory()->role('nboo_eclip_pmo')->create();
        $fms = User::factory()->role('dilg_fms')->create();

        $this->actingAs($regional)->post(route('dilg_reviewer.cases.decide', $case), [
            'decision' => 'endorsed',
        ])->assertForbidden();

        $this->actingAs($provincial)->post(route('dilg_reviewer.cases.decide', $case), [
            'decision' => 'endorsed',
            'form_8_reference' => 'FORM8-001',
            'endorsement_date' => now()->toDateString(),
        ])->assertRedirect();
        $this->assertSame(EclipCaseStatus::ProvincialEndorsed, $case->fresh()->status);

        $this->actingAs($regional)->post(route('dilg_reviewer.cases.decide', $case), [
            'decision' => 'endorsed',
            'form_9_reference' => 'FORM9-001',
            'endorsement_date' => now()->toDateString(),
        ])->assertRedirect();
        $this->assertSame(EclipCaseStatus::RegionalEndorsed, $case->fresh()->status);

        $this->actingAs($national)->post(route('dilg_reviewer.cases.decide', $case), [
            'decision' => 'approved',
            'form_10_reference' => 'FORM10-001',
            'endorsement_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame(EclipCaseStatus::Approved, $case->fresh()->status);
        $this->assertSame('approved', $request->fresh()->status);
        $this->assertSame(
            ['provincial', 'regional', 'national'],
            $case->dilgReviews()->orderBy('id')->pluck('review_level')->all(),
        );
        $this->assertDatabaseHas('eclip_dilg_reviews', [
            'assistance_revision_id' => $revision->id,
            'reviewed_by' => $national->id,
            'review_level' => 'national',
            'decision' => 'approved',
        ]);
        $this->assertCount(1, $fms->notifications()->get());
    }

    public function test_return_requires_feedback_and_allows_revision_and_resubmission(): void
    {
        [$case, $assessor, $reviewer, $request] = $this->submittedCase();

        $this->actingAs($reviewer)->post(route('dilg_reviewer.cases.decide', $case), [
            'decision' => 'returned', 'feedback' => '',
        ])->assertSessionHasErrors('feedback');

        $this->actingAs($reviewer)->post(route('dilg_reviewer.cases.decide', $case), [
            'decision' => 'returned', 'feedback' => 'Synthetic revision requested.',
        ])->assertRedirect();
        $this->assertSame(EclipCaseStatus::ReturnedForAssessmentRevision, $case->fresh()->status);
        $this->assertSame('returned', $request->fresh()->status);

        $category = EclipAssistanceCategory::query()->firstOrFail();
        $this->actingAs($assessor)->post(route('eclip_assessor.assessments.store', $case), [
            'category_id' => $category->id,
            'requested_amount' => '1200.00',
            'assessed_amount' => '850.00',
            'justification' => 'Revised synthetic justification.',
            'assessment_remarks' => 'Addressed DILG feedback.',
        ])->assertRedirect();
        $this->assertSame(EclipCaseStatus::AssistanceAssessment, $case->fresh()->status);
        $this->assertDatabaseCount('eclip_assistance_revisions', 2);

        $this->actingAs($assessor)->post(route('eclip_assessor.assessments.submit', $case))->assertRedirect();
        $this->assertSame(EclipCaseStatus::SubmittedForDilgReview, $case->fresh()->status);
        $this->assertSame('submitted', $request->fresh()->status);
        $this->assertCount(1, $reviewer->notifications()->get());
    }

    public function test_rejection_requires_feedback_and_locks_the_assessment(): void
    {
        [$case, $assessor, $reviewer] = $this->submittedCase();

        $this->actingAs($reviewer)->post(route('dilg_reviewer.cases.decide', $case), [
            'decision' => 'rejected', 'feedback' => 'Synthetic rejection reason.',
        ])->assertRedirect();

        $this->assertSame(EclipCaseStatus::Rejected, $case->fresh()->status);
        $this->actingAs($assessor)->post(route('eclip_assessor.assessments.store', $case), [
            'category_id' => EclipAssistanceCategory::query()->value('id'),
            'requested_amount' => '1.00', 'assessed_amount' => '1.00',
            'justification' => 'Unauthorized edit attempt.',
        ])->assertForbidden();
    }

    public function test_dilg_reviewer_is_restricted_to_their_municipality(): void
    {
        [$case] = $this->submittedCase();
        $otherMunicipality = Municipality::query()->create(['name' => 'Other DILG Municipality']);
        $outsideReviewer = User::factory()->role('dilg_reviewer')->create(['municipality_id' => $otherMunicipality->id]);

        $this->actingAs($outsideReviewer)->get(route('dilg_reviewer.cases.show', $case))->assertForbidden();
        $this->actingAs($outsideReviewer)->post(route('dilg_reviewer.cases.decide', $case), [
            'decision' => 'approved',
        ])->assertForbidden();
    }

    public function test_user_cannot_read_another_users_notification(): void
    {
        [, $assessor, $reviewer] = $this->submittedCase();
        $reviewer->notify(new EclipCaseActionNotification(
            EclipCase::query()->firstOrFail(), 'Synthetic private notification.', 'dilg_reviewer.cases.show',
        ));
        $notification = $reviewer->notifications()->firstOrFail();

        $this->actingAs($assessor)
            ->post(route('notifications.read', $notification))->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);
    }

    private function submittedCase(): array
    {
        $municipality = Municipality::query()->create(['name' => 'DILG Review Municipality']);
        $formerRebel = FormerRebel::query()->create([
            'classified_id' => 'FR-#DLG1', 'firstname' => 'Synthetic', 'lastname' => 'Review',
            'municipality_id' => $municipality->id,
        ]);
        $mblrc = User::factory()->role('mblrc')->create();
        $assessor = User::factory()->role('eclip_assessor')->create(['municipality_id' => $municipality->id]);
        $reviewer = User::factory()->role('dilg_reviewer')->create(['municipality_id' => $municipality->id]);
        $case = EclipCase::query()->create([
            'case_number' => 'ECLIP-DLG-000001', 'former_rebel_id' => $formerRebel->id,
            'municipality_id' => $municipality->id, 'created_by' => $mblrc->id,
            'status' => EclipCaseStatus::SubmittedForDilgReview,
        ]);
        $case->participantAssignments()->create([
            'user_id' => $assessor->id,
            'participant_role' => 'case_processor',
            'assigned_by' => $mblrc->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]);
        $category = EclipAssistanceCategory::query()->create(['code' => 'DILG_TEST', 'name' => 'DILG Test Category', 'is_active' => true]);
        $request = EclipAssistanceRequest::query()->create([
            'eclip_case_id' => $case->id, 'created_by' => $assessor->id,
            'status' => 'submitted', 'submitted_at' => now(),
        ]);
        $revision = $request->revisions()->create([
            'category_id' => $category->id, 'revision_number' => 1,
            'requested_amount' => '1000.00', 'assessed_amount' => '800.00',
            'justification' => 'Synthetic DILG review justification.', 'created_by' => $assessor->id,
        ]);

        return [$case, $assessor, $reviewer, $request, $revision];
    }
}
