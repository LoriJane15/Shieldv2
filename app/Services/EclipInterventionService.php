<?php

namespace App\Services;

use App\Models\EclipCase;
use App\Models\EclipIntervention;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EclipInterventionService
{
    public function __construct(private readonly EclipOfficialWorkflowService $officialWorkflow) {}

    public function create(EclipCase $case, array $data, User $actor, ?string $ipAddress): EclipIntervention
    {
        return DB::transaction(function () use ($case, $data, $actor, $ipAddress) {
            $data['completed_at'] = $data['status'] === 'completed' ? now() : null;
            $intervention = $case->interventions()->create([...$data, 'created_by' => $actor->id, 'updated_by' => $actor->id]);
            $intervention->histories()->create(['user_id' => $actor->id, 'action' => 'created', 'new_values' => Arr::except($intervention->attributesToArray(), ['id', 'eclip_case_id', 'created_at', 'updated_at']), 'ip_address' => $ipAddress]);
            $this->recordOfficialEvent($intervention, $actor, 'intervention_created', $ipAddress);

            return $intervention;
        });
    }

    public function update(EclipIntervention $intervention, array $data, User $actor, ?string $ipAddress): EclipIntervention
    {
        return DB::transaction(function () use ($intervention, $data, $actor, $ipAddress) {
            $locked = EclipIntervention::query()->lockForUpdate()->findOrFail($intervention->id);
            $previous = Arr::except($locked->attributesToArray(), ['updated_at']);
            $data['completed_at'] = $data['status'] === 'completed' ? ($locked->completed_at ?? now()) : null;
            $locked->update([...$data, 'updated_by' => $actor->id]);
            $locked->histories()->create([
                'user_id' => $actor->id,
                'action' => 'updated',
                'previous_values' => $previous,
                'new_values' => Arr::except($locked->fresh()->attributesToArray(), ['updated_at']),
                'ip_address' => $ipAddress,
            ]);
            $this->recordOfficialEvent($locked->fresh(), $actor, 'intervention_updated', $ipAddress);

            return $locked->fresh();
        });
    }

    private function recordOfficialEvent(EclipIntervention $intervention, User $actor, string $event, ?string $ipAddress): void
    {
        $stepCode = '4D';
        if ($intervention->stage === 'reintegration') {
            $step14 = $intervention->eclipCase->workflowActivities()->where('step_code', '14')->first();
            $stepCode = $step14 && $step14->status !== 'locked' ? '14' : '11';
        }

        $this->officialWorkflow->recordDomainEvent(
            $intervention->eclipCase,
            $stepCode,
            $actor,
            $event,
            $intervention->remarks,
            [
                'intervention_id' => $intervention->id,
                'plan_item_id' => $intervention->reintegration_plan_item_id,
                'stage' => $intervention->stage,
                'status' => $intervention->status,
                'provider' => $intervention->provider,
                'target_date' => $intervention->target_date?->toDateString(),
                'completed_at' => $intervention->completed_at?->toIso8601String(),
                'evidence_reference' => $intervention->evidence_reference,
            ],
            $ipAddress,
        );
    }
}
