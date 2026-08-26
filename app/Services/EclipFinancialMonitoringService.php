<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\EclipCase;
use App\Models\EclipLiquidationRequirement;
use App\Models\EclipRegionalDisbursementReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class EclipFinancialMonitoringService
{
    public function createLiquidation(EclipCase $case, array $data, User $actor, ?string $ipAddress, ?string $userAgent): EclipLiquidationRequirement
    {
        return DB::transaction(function () use ($case, $data, $actor, $ipAddress, $userAgent) {
            $requirement = $case->liquidationRequirements()->create([...$data, 'updated_by' => $actor->id]);
            $this->record($case, $requirement, 'liquidation_requirement_created', null, $requirement->attributesToArray(), $actor, $ipAddress, $userAgent, '8A');

            return $requirement;
        });
    }

    public function updateLiquidation(EclipLiquidationRequirement $requirement, array $data, User $actor, ?string $ipAddress, ?string $userAgent): EclipLiquidationRequirement
    {
        return DB::transaction(function () use ($requirement, $data, $actor, $ipAddress, $userAgent) {
            $locked = EclipLiquidationRequirement::query()->with('eclipCase')->lockForUpdate()->findOrFail($requirement->id);
            $previous = $this->auditValues($locked);
            $locked->update([...$data, 'updated_by' => $actor->id]);
            $this->record($locked->eclipCase, $locked, 'liquidation_requirement_updated', $previous, $this->auditValues($locked->fresh()), $actor, $ipAddress, $userAgent, '8A');

            return $locked->fresh();
        });
    }

    public function createReport(EclipCase $case, array $data, User $actor, ?string $ipAddress, ?string $userAgent): EclipRegionalDisbursementReport
    {
        return DB::transaction(function () use ($case, $data, $actor, $ipAddress, $userAgent) {
            $report = $case->regionalDisbursementReports()->create([...$data, 'updated_by' => $actor->id]);
            $this->record($case, $report, 'regional_report_created', null, $report->attributesToArray(), $actor, $ipAddress, $userAgent, '9');

            return $report;
        });
    }

    public function updateReport(EclipRegionalDisbursementReport $report, array $data, User $actor, ?string $ipAddress, ?string $userAgent): EclipRegionalDisbursementReport
    {
        return DB::transaction(function () use ($report, $data, $actor, $ipAddress, $userAgent) {
            $locked = EclipRegionalDisbursementReport::query()->with('eclipCase')->lockForUpdate()->findOrFail($report->id);
            $previous = $this->auditValues($locked);
            $locked->update([...$data, 'updated_by' => $actor->id]);
            $this->record($locked->eclipCase, $locked, 'regional_report_updated', $previous, $this->auditValues($locked->fresh()), $actor, $ipAddress, $userAgent, '9');

            return $locked->fresh();
        });
    }

    private function record(EclipCase $case, Model $entity, string $action, ?array $previous, array $new, User $actor, ?string $ipAddress, ?string $userAgent, string $stepCode): void
    {
        $activity = $case->workflowActivities()->where('step_code', $stepCode)->first();
        if ($activity) {
            $actor->loadMissing(['municipality', 'govAgency']);
            $activity->histories()->create([
                'user_id' => $actor->id,
                'event' => $action,
                'actor_role' => $actor->role,
                'actor_office' => $actor->govAgency?->name ?? $actor->municipality?->name ?? config("shield.roles.{$actor->role}.label"),
                'from_status' => $activity->status,
                'to_status' => $activity->status,
                'remarks' => $new['return_reason'] ?? $new['remarks'] ?? null,
                'data' => ['entity_id' => $entity->getKey(), 'previous' => $previous, 'new' => $new],
                'ip_address' => $ipAddress,
            ]);
        }

        AuditLog::query()->create([
            'user_id' => $actor->id,
            'action' => $action,
            'entity_type' => $entity::class,
            'entity_id' => $entity->getKey(),
            'previous_values' => $previous,
            'new_values' => $new,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    private function auditValues(Model $model): array
    {
        return Arr::except($model->attributesToArray(), ['created_at', 'updated_at']);
    }
}
