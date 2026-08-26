<?php

namespace App\Services;

use App\Models\EclipAssistanceRequest;
use App\Models\EclipCase;
use App\Models\User;
use App\Notifications\EclipCaseActionNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class EclipDilgReviewService
{
    public function __construct(
        private readonly EclipCaseWorkflowService $workflow,
        private readonly EclipOfficialWorkflowService $officialWorkflow,
    ) {}

    public function decide(
        EclipCase $case,
        User $actor,
        string $decision,
        ?string $feedback,
        ?string $ipAddress,
        array $workflowData = [],
    ): void {
        $level = $this->reviewLevel($actor);

        if (in_array($decision, ['returned', 'rejected'], true) && blank($feedback)) {
            throw ValidationException::withMessages(['feedback' => 'Feedback is required for this decision.']);
        }

        $reviewedCase = DB::transaction(function () use ($case, $actor, $level, $decision, $feedback, $ipAddress, $workflowData) {
            $request = EclipAssistanceRequest::query()->where('eclip_case_id', $case->id)->lockForUpdate()->firstOrFail();
            $revision = $request->revisions()->latest('revision_number')->firstOrFail();

            if ($request->status !== 'submitted') {
                throw ValidationException::withMessages(['status' => 'This assessment is no longer awaiting DILG review.']);
            }

            if (in_array($level, ['national', 'legacy'], true) && $decision === 'approved' && (float) $revision->assessed_amount <= 0) {
                throw ValidationException::withMessages(['decision' => 'An assessment with no approved amount cannot proceed to funding.']);
            }

            $case->dilgReviews()->create([
                'assistance_request_id' => $request->id,
                'assistance_revision_id' => $revision->id,
                'reviewed_by' => $actor->id,
                'review_level' => $level === 'legacy' ? 'national' : $level,
                'decision' => $decision,
                'feedback' => $feedback,
                'reviewed_at' => now(),
            ]);
            $request->update(['status' => in_array($decision, ['returned', 'rejected', 'approved'], true) ? $decision : 'submitted']);

            $officialStep = match ($level) {
                'provincial' => '6D',
                'regional' => '6E',
                default => '6F',
            };
            $remarks = $feedback ?: match ($officialStep) {
                '6D' => 'Submission endorsed to the DILG Regional Office.',
                '6E' => 'Submission endorsed to NBOO.',
                default => 'Request endorsed to DILG FMS for funding.',
            };

            if (in_array($decision, ['endorsed', 'approved'], true)) {
                $this->officialWorkflow->transitionDomainActivity(
                    $case,
                    $officialStep,
                    $actor,
                    'completed',
                    'review_endorsed',
                    $remarks,
                    $workflowData,
                    $ipAddress,
                );
            } elseif ($decision === 'returned') {
                $this->officialWorkflow->transitionDomainActivity(
                    $case,
                    $officialStep,
                    $actor,
                    'returned_for_correction',
                    'review_returned',
                    $feedback,
                    $workflowData,
                    $ipAddress,
                );
            } else {
                $this->officialWorkflow->recordDomainEvent(
                    $case,
                    $officialStep,
                    $actor,
                    'review_rejected',
                    $feedback,
                    $workflowData,
                    $ipAddress,
                );
            }

            return $this->workflow->decideDilgReview($case, $actor, $level, $decision, $feedback, $ipAddress);
        });

        if ($decision === 'endorsed') {
            $nextRole = $level === 'provincial' ? 'dilg_regional' : 'nboo_eclip_pmo';
            $nextOffice = $level === 'provincial' ? 'DILG Regional Office' : 'NBOO / ECLIP-PMO';
            Notification::send(
                User::query()->where('role', $nextRole)->get(),
                new EclipCaseActionNotification($reviewedCase, "An E-CLIP case was endorsed for {$nextOffice} review.", 'dilg_reviewer.cases.show'),
            );
        } else {
            Notification::send(
                User::query()->whereIn('role', ['lswdo', 'eclip_assessor'])->where('municipality_id', $reviewedCase->municipality_id)->get(),
                new EclipCaseActionNotification(
                    $reviewedCase,
                    match ($decision) {
                        'approved' => 'The E-CLIP assessment was approved after national review.',
                        'returned' => 'The E-CLIP assessment was returned for revision.',
                        default => 'The E-CLIP assessment was rejected during DILG review.',
                    },
                    'eclip_assessor.cases.show',
                ),
            );
        }

        if ($decision === 'approved') {
            Notification::send(
                User::query()->whereIn('role', ['dilg_fms', 'eclip_funding_officer'])->get(),
                new EclipCaseActionNotification($reviewedCase, 'An approved E-CLIP case requires fund allocation.', 'eclip_funding.cases.show'),
            );
        }
    }

    public function reviewLevel(User $actor): string
    {
        return match ($actor->role) {
            'dilg_provincial_focal' => 'provincial',
            'dilg_regional' => 'regional',
            'nboo_eclip_pmo' => 'national',
            'dilg_reviewer' => 'legacy',
            default => throw ValidationException::withMessages(['role' => 'Your account is not assigned to a DILG review level.']),
        };
    }
}
