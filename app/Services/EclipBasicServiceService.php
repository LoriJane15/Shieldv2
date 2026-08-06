<?php

namespace App\Services;

use App\Models\EclipBasicService;
use App\Models\EclipCase;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EclipBasicServiceService
{
    private const AUDITED = ['service_type', 'gov_agency_id', 'status', 'referral_date', 'target_completion_date', 'completed_at', 'remarks'];

    public function create(EclipCase $case, array $data, User $actor, ?string $ipAddress): EclipBasicService
    {
        return DB::transaction(function () use ($case, $data, $actor, $ipAddress) {
            $data['completed_at'] = $data['status'] === 'completed' ? now() : null;
            $service = $case->basicServices()->create([...$data, 'created_by' => $actor->id, 'updated_by' => $actor->id]);
            $service->histories()->create(['user_id' => $actor->id, 'action' => 'created', 'new_values' => Arr::only($service->attributesToArray(), self::AUDITED), 'ip_address' => $ipAddress]);

            return $service;
        });
    }

    public function update(EclipBasicService $service, array $data, User $actor, ?string $ipAddress): EclipBasicService
    {
        return DB::transaction(function () use ($service, $data, $actor, $ipAddress) {
            $locked = EclipBasicService::query()->lockForUpdate()->findOrFail($service->id);
            $before = Arr::only($locked->attributesToArray(), self::AUDITED);
            $data['completed_at'] = $data['status'] === 'completed' ? ($locked->completed_at ?? now()) : null;
            $locked->update([...$data, 'updated_by' => $actor->id]);
            $after = Arr::only($locked->fresh()->attributesToArray(), self::AUDITED);
            $locked->histories()->create(['user_id' => $actor->id, 'action' => 'updated', 'previous_values' => $before, 'new_values' => $after, 'ip_address' => $ipAddress]);

            return $locked->fresh();
        });
    }
}
