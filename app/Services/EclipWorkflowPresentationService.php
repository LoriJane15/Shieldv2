<?php

namespace App\Services;

use App\Models\EclipCase;
use App\Models\EclipWorkflowActivity;
use App\Models\User;
use Illuminate\Support\Collection;

class EclipWorkflowPresentationService
{
    public function forCase(EclipCase $case, User $user): array
    {
        $activities = $case->workflowActivities;
        $activitiesByCode = $activities->keyBy('step_code');
        $definitions = collect(config('eclip_workflow.steps', []))->keyBy('code');
        $actionableStatuses = ['pending', 'ongoing', 'late', 'returned_for_correction'];
        $finishedStatuses = ['completed', 'not_applicable'];

        $activityMeta = $activities->mapWithKeys(function (EclipWorkflowActivity $activity) use ($case, $user, $definitions, $activitiesByCode, $actionableStatuses, $finishedStatuses) {
            $activity->setRelation('eclipCase', $case);
            $definition = $definitions->get($activity->step_code, []);
            $dependencyCodes = collect($definition['depends_on'] ?? []);
            $dependencies = $dependencyCodes->map(function (string $code) use ($activitiesByCode, $definitions, $finishedStatuses) {
                $dependencyActivity = $activitiesByCode->get($code);

                return [
                    'code' => $code,
                    'title' => $dependencyActivity?->title ?? data_get($definitions, "{$code}.title", "Step {$code}"),
                    'complete' => $dependencyActivity && in_array($dependencyActivity->status, $finishedStatuses, true),
                ];
            })->values();

            return [$activity->id => [
                'responsible' => $this->roleLabels($activity->responsible_roles ?? []),
                'dependencies' => $dependencies,
                'blocking_dependencies' => $dependencies->where('complete', false)->values(),
                'is_actionable' => in_array($activity->status, $actionableStatuses, true),
                'can_update' => $user->can('updateWorkflowActivity', $activity),
            ]];
        });

        $nextActivity = $activities->first(fn (EclipWorkflowActivity $activity) => in_array($activity->status, $actionableStatuses, true));
        $firstUnfinished = $nextActivity ?? $activities->first(fn (EclipWorkflowActivity $activity) => ! in_array($activity->status, $finishedStatuses, true));
        $currentPhase = $firstUnfinished?->phase ?? $activities->max('phase');
        $finished = $activities->whereIn('status', $finishedStatuses)->count();

        $phases = $activities->groupBy('phase')->sortKeys()->map(function (Collection $phaseActivities, int $phase) use ($currentPhase, $finishedStatuses) {
            $finishedCount = $phaseActivities->whereIn('status', $finishedStatuses)->count();
            $hasAttention = $phaseActivities->contains(fn (EclipWorkflowActivity $activity) => in_array($activity->status, ['late', 'returned_for_correction'], true));

            return [
                'number' => $phase,
                'name' => config("eclip_workflow.phases.{$phase}.name", "Phase {$phase}"),
                'description' => config("eclip_workflow.phases.{$phase}.description"),
                'activities' => $phaseActivities,
                'finished' => $finishedCount,
                'total' => $phaseActivities->count(),
                'state' => $finishedCount === $phaseActivities->count()
                    ? 'completed'
                    : ($hasAttention ? 'attention' : ($phase === $currentPhase ? 'active' : 'future')),
            ];
        });

        $lastUpdated = collect([
            $case->updated_at,
            $activities->max('updated_at'),
            $case->statusHistories->max('created_at'),
        ])->filter()->sortDesc()->first();

        return [
            'activities' => $activities,
            'activity_meta' => $activityMeta,
            'next_activity' => $nextActivity,
            'next_meta' => $nextActivity ? $activityMeta->get($nextActivity->id) : null,
            'current_phase' => $currentPhase,
            'phases' => $phases,
            'counts' => [
                'total' => $activities->count(),
                'completed' => $finished,
                'pending' => $activities->whereIn('status', $actionableStatuses)->count(),
                'locked' => $activities->where('status', 'locked')->count(),
            ],
            'percent' => $activities->isEmpty() ? 0 : (int) round(($finished / $activities->count()) * 100),
            'last_updated' => $lastUpdated,
        ];
    }

    private function roleLabels(array $roles): array
    {
        if ($roles === []) {
            return ['System'];
        }

        return collect($roles)
            ->map(fn (string $role) => config("shield.roles.{$role}.label", str($role)->replace('_', ' ')->title()->toString()))
            ->values()
            ->all();
    }
}
