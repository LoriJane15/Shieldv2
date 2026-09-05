<?php

namespace App\Services;

use App\Enums\JapicCertificationHistoryEvent;
use App\Enums\JapicCertificationStatus;
use App\Models\AuditLog;
use App\Models\Ib39FrCancellation;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\JapicCertificationProcessing;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class Ib39SurfacedFormerRebelCancellationService
{
    public function cancel(
        Ib39SurfacedFormerRebel $record,
        string $reason,
        User $actor,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Ib39FrCancellation {
        abort_unless($actor->is_active && $actor->hasRole('39th_ib'), 403);

        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 2000) {
            throw ValidationException::withMessages([
                'reason' => 'The cancellation reason is required and may not exceed 2000 characters.',
            ]);
        }

        try {
            return DB::transaction(function () use ($record, $reason, $actor, $ipAddress, $userAgent) {
                $locked = Ib39SurfacedFormerRebel::query()
                    ->lockForUpdate()
                    ->findOrFail($record->getKey());

                if ($locked->cancellation()->exists()) {
                    throw ValidationException::withMessages([
                        'reason' => 'This surfaced FR has already been cancelled.',
                    ]);
                }

                $locked->load('cdrProcessing');
                $previousOverallStatus = $locked->overall_case_status;
                $cancelledAt = now();

                $cancellation = $locked->cancellation()->forceCreate([
                    'previous_overall_status' => $previousOverallStatus,
                    'reason' => $reason,
                    'cancelled_by' => $actor->id,
                    'cancelled_at' => $cancelledAt,
                ]);

                $this->cancelIncompleteJapicProcessing(
                    $locked,
                    $actor,
                    $cancelledAt,
                    $ipAddress,
                    $userAgent,
                );

                AuditLog::query()->create([
                    'user_id' => $actor->id,
                    'action' => 'ib39_surfaced_former_rebel_cancelled',
                    'entity_type' => Ib39SurfacedFormerRebel::class,
                    'entity_id' => $locked->id,
                    'previous_values' => ['overall_status' => $previousOverallStatus],
                    'new_values' => ['overall_status' => Ib39SurfacedFormerRebel::OVERALL_CASE_STATUS_CANCELLED],
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent ? mb_substr($userAgent, 0, 1000) : null,
                ]);

                return $cancellation;
            }, 5);
        } catch (Throwable $exception) {
            if ($exception instanceof QueryException
                && Ib39FrCancellation::query()->where('ib39_surfaced_former_rebel_id', $record->getKey())->exists()) {
                throw ValidationException::withMessages([
                    'reason' => 'This surfaced FR has already been cancelled.',
                ]);
            }

            throw $exception;
        }
    }

    private function cancelIncompleteJapicProcessing(
        Ib39SurfacedFormerRebel $record,
        User $actor,
        CarbonInterface $cancelledAt,
        ?string $ipAddress,
        ?string $userAgent,
    ): void {
        $processing = JapicCertificationProcessing::query()
            ->where('ib39_surfaced_former_rebel_id', $record->id)
            ->lockForUpdate()
            ->first();

        if (! $processing || in_array($processing->status, [
            JapicCertificationStatus::Completed,
            JapicCertificationStatus::Cancelled,
        ], true)) {
            return;
        }

        $fromStatus = $processing->status;
        $processing->forceFill(['status' => JapicCertificationStatus::Cancelled])->save();
        $processing->histories()->forceCreate([
            'user_id' => $actor->id,
            'from_status' => $fromStatus,
            'to_status' => JapicCertificationStatus::Cancelled,
            'event' => JapicCertificationHistoryEvent::ParentFrCancelled,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 1000) : null,
            'created_at' => $cancelledAt,
            'updated_at' => $cancelledAt,
        ]);
    }
}
