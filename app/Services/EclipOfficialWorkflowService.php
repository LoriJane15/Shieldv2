<?php

namespace App\Services;

use App\Models\EclipCase;
use App\Models\EclipWorkflowActivity;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EclipOfficialWorkflowService
{
    public function initialize(EclipCase $case, ?User $actor = null, ?string $ipAddress = null, array $verifiedEvidence = []): void
    {
        DB::transaction(function () use ($case, $actor, $ipAddress, $verifiedEvidence) {
            foreach (config('eclip_workflow.steps', []) as $definition) {
                $evidence = isset($definition['evidence_key'])
                    ? ($verifiedEvidence[$definition['evidence_key']] ?? null)
                    : null;
                $systemCompleted = is_array($evidence)
                    && filled($evidence['source'] ?? null)
                    && filled($evidence['source_record'] ?? null);
                $firstManualStep = $definition['code'] === '1' && ! $systemCompleted;
                $activity = $case->workflowActivities()->firstOrCreate(
                    ['step_code' => $definition['code']],
                    [
                        'phase' => $definition['phase'],
                        'title' => $definition['title'],
                        'status' => $systemCompleted ? 'completed' : ($firstManualStep ? 'pending' : 'locked'),
                        'responsible_roles' => $definition['roles'],
                        'required_documents' => $definition['documents'] ?? [],
                        'available_at' => $systemCompleted || $firstManualStep ? now() : null,
                        'completed_at' => $systemCompleted ? now() : null,
                        'completed_by' => null,
                        'data' => $systemCompleted ? [
                            'system_completed' => true,
                            'verified_source' => $evidence['source'],
                            'source_record' => $evidence['source_record'],
                        ] : ['system_completed' => false],
                    ],
                );

                if ($activity->wasRecentlyCreated) {
                    $activity->histories()->create([
                        'user_id' => $actor?->id,
                        'to_status' => $activity->status,
                        'remarks' => $systemCompleted
                            ? 'System-completed from verified upstream evidence.'
                            : 'Official workflow initialized.',
                        'ip_address' => $ipAddress,
                    ]);
                }
            }

            $this->unlockReadyActivities($case, $actor, $ipAddress);
        });
    }

    public function recordEligibility(EclipCase $case, User $actor, string $decision, ?string $remarks, ?string $ipAddress): void
    {
        $this->initialize($case, $actor, $ipAddress);
        $activity = $case->workflowActivities()->where('step_code', '3A')->firstOrFail();

        $status = match ($decision) {
            'eligible' => 'completed',
            'previously_assisted' => 'previously_assisted',
            default => 'not_eligible',
        };

        $this->update($activity, $actor, $status, $remarks, ['eligibility_result' => $decision], $ipAddress);
    }

    public function update(EclipWorkflowActivity $activity, User $actor, string $status, ?string $remarks, array $data, ?string $ipAddress): EclipWorkflowActivity
    {
        return DB::transaction(function () use ($activity, $actor, $status, $remarks, $data, $ipAddress) {
            $locked = EclipWorkflowActivity::query()->with('eclipCase')->lockForUpdate()->findOrFail($activity->id);
            if (($locked->data['system_completed'] ?? false) === true) {
                throw ValidationException::withMessages(['status' => 'A system-completed activity cannot be changed manually.']);
            }
            $definition = $this->definition($locked->step_code);
            $allowed = match ($locked->status) {
                'pending', 'late' => ['ongoing', 'completed', ...(($definition['allow_na'] ?? false) ? ['not_applicable'] : []), ...($this->canReturn($locked->step_code) ? ['returned_for_correction'] : [])],
                'ongoing' => ['completed', ...(($definition['allow_na'] ?? false) ? ['not_applicable'] : []), ...($this->canReturn($locked->step_code) ? ['returned_for_correction'] : [])],
                default => [],
            };

            if ($locked->step_code === '3A' && $status === 'not_eligible') {
                $allowed[] = 'not_eligible';
            }
            if ($locked->step_code === '3A' && $status === 'previously_assisted') {
                $allowed[] = 'previously_assisted';
            }
            if ($locked->step_code === '4A' && $status === 'not_authenticated') {
                $allowed[] = 'not_authenticated';
            }

            if (! in_array($status, $allowed, true)) {
                throw ValidationException::withMessages(['status' => 'This activity cannot move to the requested status.']);
            }

            if (in_array($status, ['not_eligible', 'previously_assisted', 'not_authenticated', 'returned_for_correction'], true) && blank($remarks)) {
                throw ValidationException::withMessages(['remarks' => 'Remarks are required for this status.']);
            }

            $from = $locked->status;
            $attributes = ['status' => $status, 'remarks' => $remarks, 'data' => [...($locked->data ?? []), ...$data]];
            if ($status === 'ongoing') {
                $attributes['started_at'] = $locked->started_at ?? now();
            }
            if (in_array($status, ['completed', 'not_applicable', 'not_eligible', 'previously_assisted', 'not_authenticated'], true)) {
                $attributes += ['completed_at' => now(), 'completed_by' => $actor->id];
            }
            $locked->update($attributes);
            $locked->histories()->create(['user_id' => $actor->id, 'from_status' => $from, 'to_status' => $status, 'remarks' => $remarks, 'data' => $data, 'ip_address' => $ipAddress]);

            if (in_array($status, ['completed', 'not_applicable'], true)) {
                $this->unlockReadyActivities($locked->eclipCase, $actor, $ipAddress);
            } elseif ($status === 'returned_for_correction') {
                $this->returnToPreviousActivity($locked, $actor, $remarks, $ipAddress);
            } elseif (in_array($status, ['not_eligible', 'previously_assisted', 'not_authenticated'], true)) {
                $locked->eclipCase->workflowActivities()->where('id', '!=', $locked->id)->whereNotIn('status', ['completed', 'not_applicable'])->update(['status' => 'locked']);
            }

            return $locked->fresh();
        });
    }

    private function unlockReadyActivities(EclipCase $case, ?User $actor, ?string $ipAddress): void
    {
        $activities = $case->workflowActivities()->get()->keyBy('step_code');
        foreach (config('eclip_workflow.steps', []) as $definition) {
            $activity = $activities->get($definition['code']);
            if (! $activity || $activity->status !== 'locked') {
                continue;
            }
            $dependencies = collect($definition['depends_on'] ?? []);
            if ($dependencies->isEmpty() || ! $dependencies->every(fn ($code) => in_array($activities->get($code)?->status, ['completed', 'not_applicable'], true))) {
                continue;
            }
            $availableAt = now();
            $activity->update(['status' => 'pending', 'available_at' => $availableAt, 'due_at' => $this->dueAt($availableAt, $definition['working_days'] ?? null)]);
            $activity->histories()->create(['user_id' => $actor?->id, 'from_status' => 'locked', 'to_status' => 'pending', 'remarks' => 'Prerequisite activities completed.', 'ip_address' => $ipAddress]);
        }
    }

    private function returnToPreviousActivity(EclipWorkflowActivity $activity, User $actor, ?string $remarks, ?string $ipAddress): void
    {
        $dependency = collect($this->definition($activity->step_code)['depends_on'] ?? [])->last();
        if (! $dependency) {
            return;
        }
        $previous = $activity->eclipCase->workflowActivities()->where('step_code', $dependency)->first();
        if ($previous) {
            $from = $previous->status;
            $previous->update(['status' => 'pending', 'completed_at' => null, 'completed_by' => null, 'remarks' => $remarks]);
            $previous->histories()->create(['user_id' => $actor->id, 'from_status' => $from, 'to_status' => 'pending', 'remarks' => $remarks, 'ip_address' => $ipAddress]);
        }
    }

    private function dueAt($from, ?int $workingDays): ?CarbonImmutable
    {
        if (! $workingDays) {
            return null;
        }
        $date = CarbonImmutable::parse($from);
        while ($workingDays > 0) {
            $date = $date->addDay();
            if (! $date->isWeekend()) {
                $workingDays--;
            }
        }

        return $date->endOfDay();
    }

    private function definition(string $code): array
    {
        return collect(config('eclip_workflow.steps', []))->firstWhere('code', $code)
            ?? throw ValidationException::withMessages(['step' => 'Unknown official workflow step.']);
    }

    private function canReturn(string $code): bool
    {
        return in_array($code, ['4A', '6D', '6E', '6F'], true);
    }
}
