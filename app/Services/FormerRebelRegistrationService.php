<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\FormerRebel;
use App\Models\FormerRebelRegistrationDraft;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FormerRebelRegistrationService
{
    public function saveDraft(
        array $data,
        User $actor,
        bool $explicitSave,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): FormerRebelRegistrationDraft {
        abort_unless($actor->hasRole('mblrc'), 403);

        return DB::transaction(function () use ($data, $actor, $explicitSave, $ipAddress, $userAgent) {
            $existing = FormerRebelRegistrationDraft::query()
                ->where('user_id', $actor->id)
                ->lockForUpdate()
                ->first();
            $draft = FormerRebelRegistrationDraft::query()->updateOrCreate(
                ['user_id' => $actor->id],
                ['payload' => $data, 'saved_at' => now()]
            );

            if ($explicitSave || ! $existing) {
                AuditLog::query()->create([
                    'user_id' => $actor->id,
                    'action' => 'former_rebel_registration_draft_saved',
                    'entity_type' => FormerRebelRegistrationDraft::class,
                    'entity_id' => $draft->id,
                    'previous_values' => null,
                    'new_values' => ['fields_saved' => array_values(array_keys($data))],
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                ]);
            }

            return $draft;
        }, 3);
    }

    public function register(
        array $data,
        User $actor,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): FormerRebel {
        abort_unless($actor->hasRole('mblrc'), 403);

        return DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent) {
            $data['classified_id'] = FormerRebel::nextClassifiedId();
            $data['province'] = 'Davao del Sur';
            $data['status'] ??= 'Active';
            $data['registered_at'] = now();
            $data['age'] = (int) Carbon::parse($data['birthdate'])->startOfDay()->diffInYears(now()->startOfDay());

            $formerRebel = FormerRebel::query()->create($data);

            AuditLog::query()->create([
                'user_id' => $actor->id,
                'action' => 'former_rebel_registered',
                'entity_type' => FormerRebel::class,
                'entity_id' => $formerRebel->id,
                'previous_values' => null,
                'new_values' => [
                    'classified_id' => $formerRebel->classified_id,
                    'municipality_id' => $formerRebel->municipality_id,
                    'barangay_id' => $formerRebel->barangay_id,
                    'status' => $formerRebel->status,
                ],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            FormerRebelRegistrationDraft::query()->where('user_id', $actor->id)->delete();

            return $formerRebel;
        }, 3);
    }
}
