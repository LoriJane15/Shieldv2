<?php

namespace App\Services;

use App\Models\EclipAssistanceCategory;
use App\Models\EclipAssistanceRequest;
use App\Models\EclipAssistanceRevision;
use App\Models\EclipCase;
use App\Models\User;
use App\Notifications\EclipCaseActionNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class EclipAssistanceAssessmentService
{
    public function __construct(private readonly EclipCaseWorkflowService $workflow) {}

    public function saveRevision(EclipCase $case, array $data, User $actor, ?string $ipAddress): EclipAssistanceRevision
    {
        $category = EclipAssistanceCategory::query()->findOrFail($data['category_id']);
        if (! $category->is_active) {
            throw ValidationException::withMessages(['category_id' => 'This assistance category is inactive.']);
        }

        return DB::transaction(function () use ($case, $data, $actor, $ipAddress) {
            $this->workflow->beginAssistanceAssessment($case, $actor, $ipAddress);
            $request = EclipAssistanceRequest::query()->firstOrCreate(
                ['eclip_case_id' => $case->id],
                ['created_by' => $actor->id, 'status' => 'draft'],
            );
            $lockedRequest = EclipAssistanceRequest::query()->lockForUpdate()->findOrFail($request->id);
            if (! in_array($lockedRequest->status, ['draft', 'returned'], true)) {
                throw ValidationException::withMessages(['status' => 'A submitted assessment cannot be edited.']);
            }

            $lockedRequest->update(['status' => 'draft', 'submitted_at' => null]);

            return $lockedRequest->revisions()->create([
                ...$data,
                'revision_number' => ((int) $lockedRequest->revisions()->max('revision_number')) + 1,
                'created_by' => $actor->id,
            ]);
        });
    }

    public function submit(EclipCase $case, User $actor, ?string $ipAddress): void
    {
        $submittedCase = DB::transaction(function () use ($case, $actor, $ipAddress) {
            $request = EclipAssistanceRequest::query()->where('eclip_case_id', $case->id)->lockForUpdate()->first();
            $revision = $request?->revisions()->latest('revision_number')->first();

            if (! $request || $request->status !== 'draft' || ! $revision || $revision->assessed_amount === null) {
                throw ValidationException::withMessages(['assessment' => 'A completed draft with an assessed amount is required.']);
            }

            if (! $revision->category()->where('is_active', true)->exists()) {
                throw ValidationException::withMessages(['category_id' => 'The selected assistance category is no longer active.']);
            }

            $request->update(['status' => 'submitted', 'submitted_at' => now()]);

            return $this->workflow->submitForDilgReview($case, $actor, $ipAddress);
        });

        Notification::send(
            User::query()->whereIn('role', ['dilg_provincial_focal', 'dilg_reviewer'])->where('municipality_id', $submittedCase->municipality_id)->get(),
            new EclipCaseActionNotification($submittedCase, 'An E-CLIP assessment requires Provincial validation.', 'dilg_reviewer.cases.show'),
        );
    }
}
