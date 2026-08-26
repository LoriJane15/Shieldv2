<?php

namespace App\Services;

use App\Models\EclipCase;
use App\Models\User;
use App\Notifications\EclipCaseActionNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EclipFeaAssignmentService
{
    public function assign(EclipCase $case, User $processor, User $actor, ?string $ipAddress): void
    {
        DB::transaction(function () use ($case, $processor, $actor, $ipAddress) {
            $lockedCase = EclipCase::query()->lockForUpdate()->findOrFail($case->id);
            $lockedProcessor = User::query()->lockForUpdate()->findOrFail($processor->id);

            if (! $lockedProcessor->is_active || ! $lockedProcessor->hasRole('pnp', 'afp')) {
                throw ValidationException::withMessages(['processor_id' => 'Select an active PNP or AFP processor.']);
            }

            $lockedCase->participantAssignments()
                ->where('participant_role', 'fea_processor')
                ->where('is_active', true)
                ->where('user_id', '!=', $lockedProcessor->id)
                ->update(['is_active' => false, 'ended_at' => now()]);

            $lockedCase->participantAssignments()->updateOrCreate(
                ['user_id' => $lockedProcessor->id, 'participant_role' => 'fea_processor'],
                ['assigned_by' => $actor->id, 'assigned_at' => now(), 'ended_at' => null, 'is_active' => true],
            );

            $activity = $lockedCase->workflowActivities()->where('step_code', '4B')->lockForUpdate()->firstOrFail();
            $activity->histories()->create([
                'user_id' => $actor->id,
                'from_status' => $activity->status,
                'to_status' => $activity->status,
                'remarks' => 'FEA processor assigned.',
                'data' => ['assigned_processor_id' => $lockedProcessor->id, 'assigned_processor_role' => $lockedProcessor->role],
                'ip_address' => $ipAddress,
            ]);

            $lockedProcessor->notify(new EclipCaseActionNotification(
                $lockedCase,
                'You were assigned to process FEA records for this E-CLIP case.',
                $lockedProcessor->role.'.eclip-fea.show',
            ));
        }, 3);
    }
}
