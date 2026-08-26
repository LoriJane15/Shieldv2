<?php

namespace App\Services;

use App\Enums\EclipCaseStatus;
use App\Models\AuditLog;
use App\Models\EclipCase;
use App\Models\LswdoReferral;
use App\Models\MblrcEnrollment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MblrcReferralService
{
    public function completeIntegration(
        MblrcEnrollment $enrollment,
        array $data,
        User $actor,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): LswdoReferral {
        return DB::transaction(function () use ($enrollment, $data, $actor, $ipAddress, $userAgent) {
            $locked = MblrcEnrollment::query()->lockForUpdate()->findOrFail($enrollment->id);

            if ($locked->referral()->exists()) {
                $this->synchronizeCompletedProfile($locked, $actor);

                return $locked->referral()->firstOrFail();
            }

            $startedAt = $locked->integration_started_at;
            $completedAt = CarbonImmutable::parse($data['integration_completed_at']);
            if (! $startedAt || $completedAt->lt($startedAt->copy()->addMonthsNoOverflow(3))) {
                throw ValidationException::withMessages([
                    'integration_completed_at' => 'The three-month integration period must be completed before referral.',
                ]);
            }

            $evidence = $data['phase_one_evidence'] ?? [];
            foreach (['intention_to_surface'] as $requiredEvidence) {
                if (blank($evidence[$requiredEvidence]['source'] ?? null) || blank($evidence[$requiredEvidence]['source_record'] ?? null)) {
                    throw ValidationException::withMessages([
                        "phase_one_evidence.{$requiredEvidence}" => 'Verified source and source record are required.',
                    ]);
                }
            }

            $previousEnrollmentStatus = $locked->status;
            $locked->update([
                'status' => 'completed',
                'integration_completed_at' => $completedAt->toDateString(),
                'verified_municipality_id' => $data['verified_municipality_id'],
                'location_verification_remarks' => $data['location_verification_remarks'] ?? null,
                'phase_one_evidence' => $evidence,
            ]);
            $profileChanges = $this->synchronizeCompletedProfile($locked, $actor);

            $eligibleLswdoUsers = User::query()
                ->where('role', 'lswdo')
                ->where('municipality_id', $data['verified_municipality_id'])
                ->where('is_active', true)
                ->get();
            $assignee = $eligibleLswdoUsers->count() === 1 ? $eligibleLswdoUsers->first() : null;

            $referral = LswdoReferral::query()->create([
                'referral_number' => 'LSWDO-'.Str::upper((string) Str::ulid()),
                'mblrc_enrollment_id' => $locked->id,
                'former_rebel_id' => $locked->former_rebel_id,
                'municipality_id' => $data['verified_municipality_id'],
                'assigned_to' => $assignee?->id,
                'created_by' => $actor->id,
                'status' => 'pending',
                'referred_at' => now(),
            ]);

            AuditLog::query()->create([
                'user_id' => $actor->id,
                'action' => 'integration_enrollment_completed',
                'entity_type' => MblrcEnrollment::class,
                'entity_id' => $locked->id,
                'previous_values' => [
                    'status' => $previousEnrollmentStatus,
                    'profile_status' => $profileChanges['previous_profile_status'],
                    'program_status' => $profileChanges['previous_program_status'],
                ],
                'new_values' => [
                    'status' => 'completed',
                    'integration_completed_at' => $completedAt->toDateString(),
                    'verified_municipality_id' => (int) $data['verified_municipality_id'],
                    'profile_status' => $profileChanges['profile_status'],
                    'program_status' => 'Completed',
                    'referral_id' => $referral->id,
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return $referral;
        }, 3);
    }

    private function synchronizeCompletedProfile(MblrcEnrollment $enrollment, User $actor): array
    {
        $formerRebel = $enrollment->formerRebel()->lockForUpdate()->firstOrFail();
        $programStatus = $formerRebel->programStatus()->first();
        $previousProfileStatus = $formerRebel->status;
        $previousProgramStatus = $programStatus?->reintegration_status;

        if (in_array($formerRebel->status, ['Active', 'Completed'], true)) {
            $formerRebel->update(['status' => 'Reintegrated']);
        }

        $formerRebel->programStatus()->updateOrCreate(
            ['former_rebel_id' => $formerRebel->id],
            [
                'reintegration_status' => 'Completed',
                'reintegration_date' => $enrollment->integration_completed_at?->toDateString(),
                'updated_by' => $actor->name,
            ],
        );

        return [
            'previous_profile_status' => $previousProfileStatus,
            'previous_program_status' => $previousProgramStatus,
            'profile_status' => $formerRebel->fresh()->status,
        ];
    }

    public function accept(
        LswdoReferral $referral,
        User $actor,
        EclipOfficialWorkflowService $workflow,
        ?string $ipAddress,
    ): EclipCase {
        return DB::transaction(function () use ($referral, $actor, $workflow, $ipAddress) {
            $locked = LswdoReferral::query()->with('enrollment')->lockForUpdate()->findOrFail($referral->id);

            if ($locked->assigned_to !== $actor->id || ! $actor->hasRole('lswdo')) {
                abort(403, 'This referral is not assigned to you.');
            }

            if (! in_array($locked->status, ['pending', 'accepted'], true)) {
                throw ValidationException::withMessages(['status' => 'This referral cannot be accepted.']);
            }

            $case = EclipCase::query()->where('lswdo_referral_id', $locked->id)->lockForUpdate()->first();
            if (! $case) {
                $case = EclipCase::query()->create([
                    'case_number' => 'ECLIP-'.Str::upper((string) Str::ulid()),
                    'former_rebel_id' => $locked->former_rebel_id,
                    'lswdo_referral_id' => $locked->id,
                    'municipality_id' => $locked->municipality_id,
                    'created_by' => $locked->created_by,
                    'assigned_to' => $actor->id,
                    'status' => EclipCaseStatus::SubmittedForEligibility,
                    'submitted_at' => now(),
                ]);
                $case->statusHistories()->create([
                    'user_id' => $actor->id,
                    'to_status' => EclipCaseStatus::SubmittedForEligibility->value,
                    'remarks' => 'Created from accepted MBLRC referral after verified integration completion.',
                    'ip_address' => $ipAddress,
                ]);
            }

            $case->participantAssignments()->updateOrCreate(
                ['user_id' => $actor->id, 'participant_role' => 'case_processor'],
                ['assigned_by' => $actor->id, 'assigned_at' => now(), 'ended_at' => null, 'is_active' => true],
            );

            $locked->update([
                'status' => 'accepted',
                'accepted_by' => $actor->id,
                'accepted_at' => $locked->accepted_at ?? now(),
            ]);

            $workflow->initialize($case, $actor, $ipAddress, $locked->enrollment->phase_one_evidence ?? []);

            return $case->fresh();
        }, 3);
    }
}
