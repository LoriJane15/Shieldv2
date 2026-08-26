<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\FormerRebel;
use App\Models\MblrcEnrollment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MblrcEnrollmentService
{
    public function bypassMonitoringPeriodForTesting(
        MblrcEnrollment $enrollment,
        User $actor,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): MblrcEnrollment {
        abort_unless(app()->environment(['local', 'testing']), 403);
        abort_unless($actor->hasRole('mblrc') && $enrollment->assigned_user_id === $actor->id, 403);

        return DB::transaction(function () use ($enrollment, $actor, $ipAddress, $userAgent) {
            $locked = MblrcEnrollment::query()->lockForUpdate()->findOrFail($enrollment->id);

            if ($locked->status !== 'in_progress' || $locked->referral()->exists()) {
                throw ValidationException::withMessages([
                    'enrollment' => 'Only an active monitoring enrollment without a referral can be fast-forwarded.',
                ]);
            }

            if ($locked->needsAttention()) {
                throw ValidationException::withMessages([
                    'enrollment' => 'The three-month monitoring period has already elapsed.',
                ]);
            }

            $previousStartedAt = $locked->integration_started_at?->toDateString();
            $fastForwardedStartedAt = CarbonImmutable::now(config('app.display_timezone'))
                ->startOfDay()
                ->subMonthsNoOverflow(3);

            $locked->update(['integration_started_at' => $fastForwardedStartedAt->toDateString()]);

            AuditLog::query()->create([
                'user_id' => $actor->id,
                'action' => 'integration_monitoring_period_bypassed_for_testing',
                'entity_type' => MblrcEnrollment::class,
                'entity_id' => $locked->id,
                'previous_values' => ['integration_started_at' => $previousStartedAt],
                'new_values' => [
                    'integration_started_at' => $fastForwardedStartedAt->toDateString(),
                    'testing_only' => true,
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return $locked->fresh();
        }, 3);
    }

    public function start(
        int $formerRebelId,
        string $startedAt,
        User $actor,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): MblrcEnrollment {
        abort_unless($actor->hasRole('mblrc'), 403);

        return DB::transaction(function () use ($formerRebelId, $startedAt, $actor, $ipAddress, $userAgent) {
            $formerRebel = FormerRebel::query()->lockForUpdate()->findOrFail($formerRebelId);
            $existing = MblrcEnrollment::query()
                ->where('former_rebel_id', $formerRebel->id)
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'former_rebel_id' => $existing->status === 'in_progress'
                        ? 'This beneficiary already has an active integration enrollment.'
                        : 'This beneficiary already has a completed integration enrollment.',
                ]);
            }

            $enrollment = MblrcEnrollment::query()->create([
                'former_rebel_id' => $formerRebel->id,
                'assigned_user_id' => $actor->id,
                'created_by' => $actor->id,
                'status' => 'in_progress',
                'integration_started_at' => $startedAt,
            ]);

            $formerRebel->programStatus()->updateOrCreate(
                ['former_rebel_id' => $formerRebel->id],
                [
                    'reintegration_status' => 'On-going',
                    'reintegration_date' => null,
                    'updated_by' => $actor->name,
                ],
            );

            AuditLog::query()->create([
                'user_id' => $actor->id,
                'action' => 'integration_enrollment_started',
                'entity_type' => MblrcEnrollment::class,
                'entity_id' => $enrollment->id,
                'previous_values' => null,
                'new_values' => [
                    'former_rebel_id' => $formerRebel->id,
                    'status' => $enrollment->status,
                    'program_status' => 'On-going',
                    'integration_started_at' => $enrollment->integration_started_at?->toDateString(),
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return $enrollment;
        }, 3);
    }
}
