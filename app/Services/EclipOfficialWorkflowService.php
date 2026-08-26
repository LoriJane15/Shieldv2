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
                        ...$this->actorContext($actor),
                        'event' => $systemCompleted ? 'system_completed' : 'initialized',
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

    public function synchronizeEligibleIntake(EclipCase $case, User $actor, ?string $ipAddress): void
    {
        DB::transaction(function () use ($case, $actor, $ipAddress) {
            $lockedCase = EclipCase::query()->lockForUpdate()->findOrFail($case->id);
            $this->initialize($lockedCase, $actor, $ipAddress, [
                'intention_to_surface' => [
                    'source' => 'Existing E-CLIP case record',
                    'source_record' => $lockedCase->case_number,
                ],
                'receiving_unit_coordination' => [
                    'source' => 'Existing eligibility case record',
                    'source_record' => $lockedCase->case_number,
                ],
            ]);

            $completedAt = $lockedCase->eligibility_decided_at ?? $lockedCase->submitted_at ?? now();
            $reviewerId = $lockedCase->eligibilityReviews()->latest('reviewed_at')->value('reviewed_by') ?? $actor->id;
            foreach (['1', '2', '3A'] as $stepCode) {
                $activity = $lockedCase->workflowActivities()->where('step_code', $stepCode)->lockForUpdate()->firstOrFail();
                if ($activity->status === 'completed') {
                    continue;
                }

                $from = $activity->status;
                $data = [
                    ...($activity->data ?? []),
                    'compatibility_import' => true,
                    'source_record' => $lockedCase->case_number,
                    ...($stepCode === '3A' ? ['eligibility_result' => 'eligible'] : []),
                ];
                $activity->update([
                    'status' => 'completed',
                    'available_at' => $activity->available_at ?? $completedAt,
                    'completed_at' => $completedAt,
                    'completed_by' => $stepCode === '3A' ? $reviewerId : null,
                    'data' => $data,
                ]);
                $activity->histories()->create([
                    ...$this->actorContext($actor),
                    'event' => 'compatibility_imported',
                    'from_status' => $from,
                    'to_status' => 'completed',
                    'remarks' => 'Imported from the case eligibility state that predates the official workflow tracker.',
                    'data' => ['source_case_status' => $lockedCase->status->value],
                    'ip_address' => $ipAddress,
                ]);
            }

            $this->unlockReadyActivities($lockedCase, $actor, $ipAddress);
        });
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

            $mergedData = [...($locked->data ?? []), ...$data];
            if ($status === 'completed') {
                $this->ensureCanComplete($locked, $definition, $mergedData);
            }

            if (in_array($status, ['not_eligible', 'previously_assisted', 'not_authenticated', 'returned_for_correction'], true) && blank($remarks)) {
                throw ValidationException::withMessages(['remarks' => 'Remarks are required for this status.']);
            }

            $from = $locked->status;
            $attributes = ['status' => $status, 'remarks' => $remarks, 'data' => $mergedData];
            if ($status === 'ongoing') {
                $attributes['started_at'] = $locked->started_at ?? now();
            }
            if (in_array($status, ['completed', 'not_applicable', 'not_eligible', 'previously_assisted', 'not_authenticated'], true)) {
                $attributes += ['completed_at' => now(), 'completed_by' => $actor->id];
            }
            $locked->update($attributes);
            $locked->histories()->create([
                ...$this->actorContext($actor),
                'event' => 'status_changed',
                'from_status' => $from,
                'to_status' => $status,
                'remarks' => $remarks,
                'data' => $data,
                'ip_address' => $ipAddress,
            ]);

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
            if (! $activity || ! in_array($activity->status, ['locked', 'returned_for_correction'], true)) {
                continue;
            }
            $dependencies = collect($definition['depends_on'] ?? []);
            if ($dependencies->isEmpty() || ! $dependencies->every(fn ($code) => in_array($activities->get($code)?->status, ['completed', 'not_applicable'], true))) {
                continue;
            }
            $availableAt = now();
            $fromStatus = $activity->status;
            $activity->update(['status' => 'pending', 'available_at' => $availableAt, 'due_at' => $this->dueAt($availableAt, $definition['working_days'] ?? null)]);
            $activity->histories()->create([
                ...$this->actorContext($actor),
                'event' => $fromStatus === 'returned_for_correction' ? 'resubmitted' : 'unlocked',
                'from_status' => $fromStatus,
                'to_status' => 'pending',
                'remarks' => $fromStatus === 'returned_for_correction'
                    ? 'Corrected prerequisite was resubmitted.'
                    : 'Prerequisite activities completed.',
                'ip_address' => $ipAddress,
            ]);
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
            $previous->histories()->create([
                ...$this->actorContext($actor),
                'event' => 'returned',
                'from_status' => $from,
                'to_status' => 'pending',
                'remarks' => $remarks,
                'ip_address' => $ipAddress,
            ]);
        }
    }

    private function ensureCanComplete(EclipWorkflowActivity $activity, array $definition, array $data): void
    {
        if (($definition['enforce_documents'] ?? false) === true) {
            $uploadedTypes = $activity->documents()->distinct()->pluck('document_type');
            $missing = collect($definition['documents'] ?? [])->diff($uploadedTypes);
            if ($missing->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'document' => 'Upload the following required evidence before completing this step: '.$missing->join(', ').'.',
                ]);
            }
        }

        if ($activity->step_code === '4A') {
            $authentication = $activity->eclipCase->authenticationRequest;
            if (! $authentication || $authentication->status !== 'authenticated' || blank($authentication->certification_reference)) {
                throw ValidationException::withMessages(['status' => 'Record an authenticated JAPIC decision and certification reference before completing this step.']);
            }
        }

        if ($activity->step_code === '6A') {
            $missingChecklist = collect([
                'initial_interview_uploaded',
                'enrollment_form_uploaded',
                'id_uploaded',
                'japic_certification_uploaded',
            ])->reject(fn (string $key) => ($data[$key] ?? false) === true);
            if ($missingChecklist->isNotEmpty()) {
                throw ValidationException::withMessages(['status' => 'Confirm the four core E-CLIP IS files as uploaded before completing this step.']);
            }
        }

        if ($activity->step_code === '7B' && ! $activity->eclipCase->assistanceReleases()->exists()) {
            throw ValidationException::withMessages(['status' => 'Record the authorized assistance release and signed acknowledgment before completing this step.']);
        }

        if ($activity->step_code === '8A') {
            $requirements = $activity->eclipCase->liquidationRequirements()->get();
            if ($requirements->isEmpty() || $requirements->contains(fn ($requirement) => $requirement->status !== 'accepted' || ! $requirement->accepted_at)) {
                throw ValidationException::withMessages(['status' => 'Every recorded liquidation requirement must be officially accepted before this step can be marked liquidated.']);
            }
        }

        if ($activity->step_code === '9' && ! $activity->eclipCase->regionalDisbursementReports()->where('status', 'accepted')->whereNotNull('accepted_at')->exists()) {
            throw ValidationException::withMessages(['status' => 'An accepted regional disbursement report is required before completing this step.']);
        }

        if ($activity->step_code !== '14') {
            return;
        }

        $interventions = $activity->eclipCase->interventions()->where('stage', 'reintegration')->get();
        if ($interventions->isEmpty()) {
            throw ValidationException::withMessages(['status' => 'Record the applicable reintegration interventions before closing the case.']);
        }

        $unfinished = $interventions->first(fn ($intervention) => ! in_array($intervention->status, ['completed', 'not_applicable'], true)
            || ($intervention->status === 'completed' && blank($intervention->outcome))
            || ($intervention->status === 'not_applicable' && blank($intervention->remarks)));
        if ($unfinished) {
            throw ValidationException::withMessages(['status' => 'Every required intervention must have a final outcome or a documented not-applicable reason before closure.']);
        }
    }

    private function actorContext(?User $actor): array
    {
        if (! $actor) {
            return ['user_id' => null, 'actor_role' => 'system', 'actor_office' => 'SHIELD 2.0'];
        }

        $actor->loadMissing(['municipality', 'govAgency']);

        return [
            'user_id' => $actor->id,
            'actor_role' => $actor->role,
            'actor_office' => $actor->govAgency?->name
                ?? $actor->municipality?->name
                ?? config("shield.roles.{$actor->role}.label", str($actor->role)->replace('_', ' ')->title()->toString()),
        ];
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
