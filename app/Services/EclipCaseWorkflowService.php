<?php

namespace App\Services;

use App\Enums\EclipCaseStatus;
use App\Models\EclipCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EclipCaseWorkflowService
{
    public function __construct(private readonly EclipOfficialWorkflowService $officialWorkflow) {}

    public function beginAssistanceRelease(EclipCase $case, User $actor, ?string $ipAddress): EclipCase
    {
        if ($case->status === EclipCaseStatus::ReleasePending) {
            return $case;
        }

        return $this->transition($case, $actor, [EclipCaseStatus::FundsTransferred], EclipCaseStatus::ReleasePending, null, $ipAddress);
    }

    public function completeAssistanceRelease(EclipCase $case, User $actor, ?string $ipAddress): EclipCase
    {
        return $this->transition($case, $actor, [EclipCaseStatus::ReleasePending], EclipCaseStatus::AssistanceReleased, null, $ipAddress);
    }

    public function beginFundAllocation(EclipCase $case, User $actor, ?string $ipAddress): EclipCase
    {
        if ($case->status === EclipCaseStatus::FundAllocationPending) {
            return $case;
        }

        return $this->transition($case, $actor, [EclipCaseStatus::Approved], EclipCaseStatus::FundAllocationPending, null, $ipAddress);
    }

    public function markFundsAllocated(EclipCase $case, User $actor, ?string $ipAddress): EclipCase
    {
        return $this->transition($case, $actor, [EclipCaseStatus::FundAllocationPending], EclipCaseStatus::FundsAllocated, null, $ipAddress);
    }

    public function markFundsTransferred(EclipCase $case, User $actor, ?string $ipAddress): EclipCase
    {
        return $this->transition($case, $actor, [EclipCaseStatus::FundsAllocated], EclipCaseStatus::FundsTransferred, null, $ipAddress);
    }

    public function beginAssistanceAssessment(EclipCase $case, User $actor, ?string $ipAddress): EclipCase
    {
        if ($case->status === EclipCaseStatus::AssistanceAssessment) {
            return $case;
        }

        return $this->transition(
            $case,
            $actor,
            [EclipCaseStatus::DocumentsCertified, EclipCaseStatus::ReturnedForAssessmentRevision],
            EclipCaseStatus::AssistanceAssessment,
            null,
            $ipAddress,
        );
    }

    public function decideDilgReview(EclipCase $case, User $actor, string $level, string $decision, ?string $feedback, ?string $ipAddress): EclipCase
    {
        $target = in_array($decision, ['returned', 'rejected'], true)
            ? match ($decision) {
                'returned' => EclipCaseStatus::ReturnedForAssessmentRevision,
                'rejected' => EclipCaseStatus::Rejected,
            }
        : match ([$level, $decision]) {
            ['provincial', 'endorsed'] => EclipCaseStatus::ProvincialEndorsed,
            ['regional', 'endorsed'] => EclipCaseStatus::RegionalEndorsed,
            ['national', 'approved'] => EclipCaseStatus::Approved,
            ['legacy', 'approved'] => EclipCaseStatus::Approved,
            default => throw ValidationException::withMessages(['decision' => 'The DILG review decision is invalid.']),
        };

        $from = match ($level) {
            'provincial' => EclipCaseStatus::SubmittedForDilgReview,
            'regional' => EclipCaseStatus::ProvincialEndorsed,
            'national' => EclipCaseStatus::RegionalEndorsed,
            'legacy' => EclipCaseStatus::SubmittedForDilgReview,
            default => throw ValidationException::withMessages(['status' => 'The DILG review level is invalid.']),
        };

        return $this->transition($case, $actor, [$from], $target, $feedback, $ipAddress);
    }

    public function submitForDilgReview(EclipCase $case, User $actor, ?string $ipAddress): EclipCase
    {
        return $this->transition($case, $actor, [EclipCaseStatus::AssistanceAssessment], EclipCaseStatus::SubmittedForDilgReview, null, $ipAddress);
    }

    public function beginDocumentProcessing(EclipCase $case, User $actor, ?string $ipAddress): EclipCase
    {
        if ($case->status === EclipCaseStatus::DocumentProcessing) {
            return $case;
        }

        return $this->transition(
            $case,
            $actor,
            [EclipCaseStatus::Eligible, EclipCaseStatus::DocumentsIncomplete],
            EclipCaseStatus::DocumentProcessing,
            null,
            $ipAddress,
        );
    }

    public function markDocumentsIncomplete(EclipCase $case, User $actor, string $remarks, ?string $ipAddress): EclipCase
    {
        if ($case->status === EclipCaseStatus::DocumentsIncomplete) {
            return $case;
        }

        return $this->transition(
            $case,
            $actor,
            [EclipCaseStatus::DocumentProcessing],
            EclipCaseStatus::DocumentsIncomplete,
            $remarks,
            $ipAddress,
        );
    }

    public function markDocumentsCertified(EclipCase $case, User $actor, ?string $ipAddress): EclipCase
    {
        return $this->transition(
            $case,
            $actor,
            [EclipCaseStatus::DocumentProcessing, EclipCaseStatus::DocumentsIncomplete],
            EclipCaseStatus::DocumentsCertified,
            null,
            $ipAddress,
        );
    }

    public function submitForEligibility(EclipCase $case, User $actor, ?string $ipAddress): EclipCase
    {
        $submitted = $this->transition(
            $case,
            $actor,
            [EclipCaseStatus::Draft, EclipCaseStatus::ReturnedForCorrection],
            EclipCaseStatus::SubmittedForEligibility,
            null,
            $ipAddress,
            ['submitted_at' => now(), 'assigned_to' => null, 'eligibility_decided_at' => null],
        );

        $this->officialWorkflow->initialize($submitted, $actor, $ipAddress, [
            'intention_to_surface' => [
                'source' => 'MBLRC submission',
                'source_record' => $submitted->case_number,
            ],
        ]);

        return $submitted;
    }

    public function decideEligibility(
        EclipCase $case,
        User $actor,
        string $decision,
        ?string $remarks,
        ?string $ipAddress,
        ?string $referralStatus = null,
        ?string $referredProgram = null,
    ): EclipCase {
        $target = match ($decision) {
            'eligible' => EclipCaseStatus::Eligible,
            'previously_assisted' => EclipCaseStatus::PreviouslyAssisted,
            'for_clarification' => EclipCaseStatus::ReturnedForCorrection,
            'not_eligible', 'ineligible' => EclipCaseStatus::Ineligible,
            'returned' => EclipCaseStatus::ForClarification,
            default => throw ValidationException::withMessages(['decision' => 'The eligibility decision is invalid.']),
        };

        if ($decision !== 'eligible' && blank($remarks)) {
            throw ValidationException::withMessages(['remarks' => 'Remarks are required for this decision.']);
        }

        return DB::transaction(function () use ($case, $actor, $decision, $remarks, $referralStatus, $referredProgram, $ipAddress, $target) {
            $lockedCase = EclipCase::query()->lockForUpdate()->findOrFail($case->id);

            if (! in_array($lockedCase->status, [
                EclipCaseStatus::SubmittedForEligibility,
                EclipCaseStatus::EligibilityReviewInProgress,
            ], true)) {
                throw ValidationException::withMessages(['status' => 'This case is no longer awaiting an eligibility decision.']);
            }

            $lockedCase->eligibilityReviews()->create([
                'reviewed_by' => $actor->id,
                'decision' => $decision,
                'referral_status' => $decision === 'not_eligible' ? $referralStatus : null,
                'referred_program' => $decision === 'not_eligible' && $referralStatus === 'referred' ? $referredProgram : null,
                'remarks' => $remarks,
                'reviewed_at' => now(),
            ]);

            $result = $this->transitionLocked($lockedCase, $actor, $target, $remarks, $ipAddress, [
                'assigned_to' => $actor->id,
                'eligibility_decided_at' => now(),
            ]);

            if (in_array($decision, ['eligible', 'ineligible', 'not_eligible', 'previously_assisted'], true)) {
                $this->officialWorkflow->recordEligibility($result, $actor, $decision, $remarks, $ipAddress, [
                    'referral_status' => $decision === 'not_eligible' ? $referralStatus : null,
                    'referred_program' => $decision === 'not_eligible' && $referralStatus === 'referred' ? $referredProgram : null,
                ]);
            }

            return $result;
        });
    }

    private function transition(
        EclipCase $case,
        User $actor,
        array $allowedFrom,
        EclipCaseStatus $target,
        ?string $remarks,
        ?string $ipAddress,
        array $attributes = [],
    ): EclipCase {
        return DB::transaction(function () use ($case, $actor, $allowedFrom, $target, $remarks, $ipAddress, $attributes) {
            $lockedCase = EclipCase::query()->lockForUpdate()->findOrFail($case->id);

            if (! in_array($lockedCase->status, $allowedFrom, true)) {
                throw ValidationException::withMessages(['status' => 'This case cannot move to the requested stage.']);
            }

            return $this->transitionLocked($lockedCase, $actor, $target, $remarks, $ipAddress, $attributes);
        });
    }

    private function transitionLocked(
        EclipCase $case,
        User $actor,
        EclipCaseStatus $target,
        ?string $remarks,
        ?string $ipAddress,
        array $attributes,
    ): EclipCase {
        $from = $case->status;
        $case->update([...$attributes, 'status' => $target]);
        $case->statusHistories()->create([
            'user_id' => $actor->id,
            'from_status' => $from?->value,
            'to_status' => $target->value,
            'remarks' => $remarks,
            'ip_address' => $ipAddress,
        ]);

        return $case->fresh();
    }
}
