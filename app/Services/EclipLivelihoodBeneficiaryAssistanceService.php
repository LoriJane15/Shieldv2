<?php

namespace App\Services;

use App\Models\EclipCase;
use App\Models\EclipLivelihoodBeneficiaryAssistance;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EclipLivelihoodBeneficiaryAssistanceService
{
    public function __construct(private readonly EclipOfficialWorkflowService $officialWorkflow) {}

    public function create(EclipCase $case, array $data, User $actor, ?string $ipAddress): EclipLivelihoodBeneficiaryAssistance
    {
        return DB::transaction(function () use ($case, $data, $actor, $ipAddress) {
            $assistance = $case->livelihoodBeneficiaryAssistances()->create([...$data, 'created_by' => $actor->id, 'updated_by' => $actor->id]);
            $assistance->histories()->create([
                'user_id' => $actor->id,
                'action' => 'created',
                'new_values' => $this->auditValues($assistance),
                'ip_address' => $ipAddress,
            ]);
            $this->recordOfficialEvent($assistance, $actor, 'livelihood_beneficiary_recorded', $ipAddress);

            return $assistance;
        });
    }

    public function update(EclipLivelihoodBeneficiaryAssistance $assistance, array $data, User $actor, ?string $ipAddress): EclipLivelihoodBeneficiaryAssistance
    {
        return DB::transaction(function () use ($assistance, $data, $actor, $ipAddress) {
            $locked = EclipLivelihoodBeneficiaryAssistance::query()->lockForUpdate()->findOrFail($assistance->id);
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
            $this->recordOfficialEvent($fresh, $actor, 'livelihood_beneficiary_updated', $ipAddress);

            return $fresh;
        });
    }

    private function recordOfficialEvent(EclipLivelihoodBeneficiaryAssistance $assistance, User $actor, string $event, ?string $ipAddress): void
    {
        $this->officialWorkflow->recordDomainEvent($assistance->eclipCase, '12', $actor, $event, $assistance->remarks, [
            'assistance_id' => $assistance->id,
            'approval_reference' => $assistance->approval_reference,
            'approval_status' => $assistance->approval_status,
            'release_status' => $assistance->release_status,
            'release_date' => $assistance->release_date?->toDateString(),
        ], $ipAddress);
    }

    private function auditValues(EclipLivelihoodBeneficiaryAssistance $assistance): array
    {
        return Arr::except($assistance->attributesToArray(), ['id', 'eclip_case_id', 'beneficiary_name', 'created_at', 'updated_at']);
    }
}
