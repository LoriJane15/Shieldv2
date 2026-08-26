<?php

namespace App\Services;

use App\Models\EclipCase;
use App\Models\EclipReintegrationPlanItem;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EclipReintegrationPlanService
{
    public function __construct(private readonly EclipOfficialWorkflowService $officialWorkflow) {}

    public function create(EclipCase $case, array $data, User $actor, ?string $ipAddress): EclipReintegrationPlanItem
    {
        return DB::transaction(function () use ($case, $data, $actor, $ipAddress) {
            $item = $case->reintegrationPlanItems()->create([...$data, 'created_by' => $actor->id, 'updated_by' => $actor->id]);
            $item->histories()->create([
                'user_id' => $actor->id,
                'action' => 'created',
                'new_values' => $this->auditValues($item),
                'ip_address' => $ipAddress,
            ]);
            $this->officialWorkflow->recordDomainEvent($case, '10', $actor, 'plan_item_created', null, [
                'plan_item_id' => $item->id,
                'proposed_assistance' => $item->proposed_assistance,
                'status' => $item->status,
            ], $ipAddress);

            return $item;
        });
    }

    public function update(EclipReintegrationPlanItem $item, array $data, User $actor, ?string $ipAddress): EclipReintegrationPlanItem
    {
        return DB::transaction(function () use ($item, $data, $actor, $ipAddress) {
            $locked = EclipReintegrationPlanItem::query()->lockForUpdate()->findOrFail($item->id);
            $previous = $this->auditValues($locked);
            $locked->update([...$data, 'updated_by' => $actor->id]);
            $fresh = $locked->fresh();
            $fresh->histories()->create([
                'user_id' => $actor->id,
                'action' => 'updated',
                'previous_values' => $previous,
                'new_values' => $this->auditValues($fresh),
                'ip_address' => $ipAddress,
            ]);
            $this->officialWorkflow->recordDomainEvent($fresh->eclipCase, '10', $actor, 'plan_item_updated', null, [
                'plan_item_id' => $fresh->id,
                'status' => $fresh->status,
            ], $ipAddress);

            return $fresh;
        });
    }

    private function auditValues(EclipReintegrationPlanItem $item): array
    {
        return Arr::except($item->attributesToArray(), ['id', 'eclip_case_id', 'created_at', 'updated_at']);
    }
}
