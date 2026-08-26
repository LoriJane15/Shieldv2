<?php

namespace App\Services;

use App\Enums\EclipCaseStatus;
use App\Models\EclipAuthenticationRequest;
use App\Models\EclipCase;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EclipAuthenticationService
{
    public function __construct(private readonly EclipOfficialWorkflowService $officialWorkflow) {}

    public function request(EclipCase $case, User $japic, User $actor, ?string $ipAddress): EclipAuthenticationRequest
    {
        return DB::transaction(function () use ($case, $japic, $actor, $ipAddress) {
            $lockedCase = EclipCase::query()->lockForUpdate()->findOrFail($case->id);
            if ($lockedCase->status !== EclipCaseStatus::Eligible || ! $lockedCase->hasActiveParticipant($actor)) {
                throw ValidationException::withMessages(['status' => 'Only the assigned processor may request authentication for an eligible case.']);
            }
            if (! $japic->hasRole('japic') || ! $japic->is_active) {
                throw ValidationException::withMessages(['assigned_to' => 'Select an active JAPIC reviewer.']);
            }

            $existing = $lockedCase->authenticationRequest()->first();
            if ($existing) {
                return $existing;
            }

            $this->officialWorkflow->synchronizeEligibleIntake($lockedCase, $actor, $ipAddress);
            $endorsement = $lockedCase->workflowActivities()->where('step_code', '3B')->firstOrFail();
            $this->officialWorkflow->update($endorsement, $actor, 'completed', 'Endorsed for JAPIC authentication.', [], $ipAddress);

            $request = $lockedCase->authenticationRequest()->create([
                'requested_by' => $actor->id,
                'assigned_to' => $japic->id,
                'status' => 'pending',
                'requested_at' => now(),
                'due_at' => $this->addWorkingDays(now(), 10),
            ]);
            $request->histories()->create([
                'user_id' => $actor->id, 'to_status' => 'pending',
                'remarks' => 'Authentication requested.', 'ip_address' => $ipAddress,
            ]);
            $lockedCase->participantAssignments()->updateOrCreate(
                ['user_id' => $japic->id, 'participant_role' => 'authentication_reviewer'],
                ['assigned_by' => $actor->id, 'assigned_at' => now(), 'ended_at' => null, 'is_active' => true],
            );
            $this->transitionCase($lockedCase, $actor, EclipCaseStatus::AuthenticationPending, null, $ipAddress);

            return $request;
        }, 3);
    }

    public function start(EclipAuthenticationRequest $request, User $actor, ?string $ipAddress): void
    {
        DB::transaction(function () use ($request, $actor, $ipAddress) {
            $locked = EclipAuthenticationRequest::query()->with('eclipCase')->lockForUpdate()->findOrFail($request->id);
            $this->authorizeAssignee($locked, $actor);
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'This authentication request cannot be started.']);
            }
            $locked->update(['status' => 'under_review', 'started_at' => now()]);
            $locked->histories()->create(['user_id' => $actor->id, 'from_status' => 'pending', 'to_status' => 'under_review', 'ip_address' => $ipAddress]);
            $activity = $locked->eclipCase->workflowActivities()->where('step_code', '4A')->firstOrFail();
            $this->officialWorkflow->update($activity, $actor, 'ongoing', null, [], $ipAddress);
            $this->transitionCase($locked->eclipCase, $actor, EclipCaseStatus::AuthenticationUnderReview, null, $ipAddress);
        });
    }

    public function decide(EclipAuthenticationRequest $request, User $actor, string $decision, ?string $certificationReference, ?string $remarks, ?string $ipAddress): void
    {
        DB::transaction(function () use ($request, $actor, $decision, $certificationReference, $remarks, $ipAddress) {
            $locked = EclipAuthenticationRequest::query()->with('eclipCase')->lockForUpdate()->findOrFail($request->id);
            $this->authorizeAssignee($locked, $actor);
            if ($locked->status !== 'under_review') {
                throw ValidationException::withMessages(['status' => 'Start the review before recording a decision.']);
            }
            if ($decision === 'authenticated' && blank($certificationReference)) {
                throw ValidationException::withMessages(['certification_reference' => 'A certification reference is required.']);
            }
            $activity = $locked->eclipCase->workflowActivities()->where('step_code', '4A')->firstOrFail();
            if ($decision === 'authenticated' && ! $activity->documents()->where('document_type', 'JAPIC Certification')->exists()) {
                throw ValidationException::withMessages(['certification_document' => 'Upload the JAPIC Certification before recording an authenticated decision.']);
            }
            if (in_array($decision, ['returned', 'not_authenticated'], true) && blank($remarks)) {
                throw ValidationException::withMessages(['remarks' => 'Remarks are required for this decision.']);
            }
            if (! in_array($decision, ['authenticated', 'returned', 'not_authenticated'], true)) {
                throw ValidationException::withMessages(['decision' => 'Invalid authentication decision.']);
            }

            $locked->update([
                'status' => $decision, 'decided_at' => now(),
                'certification_reference' => $decision === 'authenticated' ? $certificationReference : null,
                'remarks' => $remarks,
            ]);
            $locked->histories()->create([
                'user_id' => $actor->id, 'from_status' => 'under_review', 'to_status' => $decision,
                'remarks' => $remarks, 'data' => $decision === 'authenticated' ? ['certification_reference' => $certificationReference] : null,
                'ip_address' => $ipAddress,
            ]);
            $activityStatus = match ($decision) {
                'authenticated' => 'completed',
                'returned' => 'returned_for_correction',
                'not_authenticated' => 'not_authenticated',
            };
            $this->officialWorkflow->update($activity, $actor, $activityStatus, $remarks, ['certification_reference' => $certificationReference], $ipAddress);
            $caseStatus = match ($decision) {
                'authenticated' => EclipCaseStatus::Authenticated,
                'returned' => EclipCaseStatus::AuthenticationReturned,
                'not_authenticated' => EclipCaseStatus::NotAuthenticated,
            };
            $this->transitionCase($locked->eclipCase, $actor, $caseStatus, $remarks, $ipAddress);
        });
    }

    private function authorizeAssignee(EclipAuthenticationRequest $request, User $actor): void
    {
        abort_unless($actor->hasRole('japic') && $request->assigned_to === $actor->id && $request->eclipCase->hasActiveParticipant($actor, 'authentication_reviewer'), 403);
    }

    private function transitionCase(EclipCase $case, User $actor, EclipCaseStatus $to, ?string $remarks, ?string $ipAddress): void
    {
        $from = $case->status;
        if (! in_array($from, [
            EclipCaseStatus::Eligible,
            EclipCaseStatus::AuthenticationPending,
            EclipCaseStatus::AuthenticationUnderReview,
            EclipCaseStatus::AuthenticationReturned,
        ], true)) {
            return;
        }

        $case->update(['status' => $to]);
        $case->statusHistories()->create([
            'user_id' => $actor->id, 'from_status' => $from?->value, 'to_status' => $to->value,
            'remarks' => $remarks, 'ip_address' => $ipAddress,
        ]);
    }

    private function addWorkingDays($date, int $days): CarbonImmutable
    {
        $due = CarbonImmutable::parse($date);
        while ($days > 0) {
            $due = $due->addDay();
            if (! $due->isWeekend()) {
                $days--;
            }
        }

        return $due->endOfDay();
    }
}
