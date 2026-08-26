<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\FormerRebel;
use App\Models\MblrcEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MblrcEnrollmentService
{
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

            AuditLog::query()->create([
                'user_id' => $actor->id,
                'action' => 'integration_enrollment_started',
                'entity_type' => MblrcEnrollment::class,
                'entity_id' => $enrollment->id,
                'previous_values' => null,
                'new_values' => [
                    'former_rebel_id' => $formerRebel->id,
                    'status' => $enrollment->status,
                    'integration_started_at' => $enrollment->integration_started_at?->toDateString(),
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return $enrollment;
        }, 3);
    }
}
