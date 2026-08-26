<?php

namespace App\Services;

use App\Models\EclipCase;
use App\Models\EclipFeaDocument;
use App\Models\User;
use Illuminate\Support\Collection;

class EclipFeaWorkspaceService
{
    public function forCase(EclipCase $case, User $actor): array
    {
        $case->load([
            'formerRebel.municipality',
            'feaDocuments.uploader',
            'participantAssignments.user',
            'workflowActivities' => fn ($query) => $query->where('step_code', '4B')->with('histories.user'),
        ]);

        $activity = $case->workflowActivities->firstOrFail();
        $documents = $case->feaDocuments->sortByDesc('created_at')->values();
        $uploadedTypes = $documents->pluck('document_type')->unique();
        $requiredDocuments = collect(EclipFeaDocument::REQUIRED_TYPES)->map(fn (string $type) => [
            'type' => $type,
            'label' => EclipFeaDocument::TYPE_LABELS[$type],
            'uploaded' => $uploadedTypes->contains($type),
        ]);
        $completedRequirements = $requiredDocuments->where('uploaded', true)->count();
        $requirementsComplete = $completedRequirements === $requiredDocuments->count();
        $isComplete = in_array($activity->status, ['completed', 'not_applicable'], true);
        $currentStage = $isComplete ? 4 : ($requirementsComplete ? 3 : 2);

        return [
            'activity' => $activity,
            'documents' => $documents,
            'type_labels' => EclipFeaDocument::TYPE_LABELS,
            'required_documents' => $requiredDocuments,
            'required_completed' => $completedRequirements,
            'required_total' => $requiredDocuments->count(),
            'required_percent' => $requiredDocuments->isEmpty() ? 0 : (int) round(($completedRequirements / $requiredDocuments->count()) * 100),
            'requirements_complete' => $requirementsComplete,
            'status' => $this->status($activity->status, $requirementsComplete),
            'stages' => $this->stages($currentStage, $isComplete),
            'assigned_office' => config("shield.roles.{$actor->role}.label", str($actor->role)->upper()->toString()),
            'last_updated' => $this->lastUpdated($case, $activity, $documents),
            'activity_items' => $this->activityItems($activity->histories, $documents),
            'can_upload' => $actor->can('uploadFeaDocument', $case),
            'can_complete' => $requirementsComplete && $actor->can('updateWorkflowActivity', $activity),
        ];
    }

    private function status(string $activityStatus, bool $requirementsComplete): array
    {
        return match ($activityStatus) {
            'completed' => ['label' => 'Completed', 'tone' => 'complete'],
            'not_applicable' => ['label' => 'Not Applicable', 'tone' => 'neutral'],
            'late' => ['label' => 'Documents Overdue', 'tone' => 'danger'],
            'returned_for_correction' => ['label' => 'Correction Required', 'tone' => 'danger'],
            default => $requirementsComplete
                ? ['label' => 'Pending Verification', 'tone' => 'verification']
                : ['label' => 'Pending Documents', 'tone' => 'pending'],
        };
    }

    private function stages(int $currentStage, bool $isComplete): Collection
    {
        return collect(['Case Assigned', 'Documents', 'Verification', 'Completed'])
            ->map(function (string $label, int $index) use ($currentStage, $isComplete) {
                $number = $index + 1;
                $state = $isComplete || $number < $currentStage ? 'complete' : ($number === $currentStage ? 'active' : 'upcoming');

                return compact('number', 'label', 'state');
            });
    }

    private function activityItems(Collection $histories, Collection $documents): Collection
    {
        $historyDocumentIds = $histories->pluck('data.fea_document_id')->filter();
        $historyItems = $histories->map(function ($history) {
            $title = filled($history->remarks)
                ? $history->remarks
                : str($history->event ?? 'status_changed')->replace('_', ' ')->title()->toString();

            return [
                'title' => $title,
                'detail' => collect([$history->user?->name, $history->actor_office])->filter()->unique()->join(' · '),
                'occurred_at' => $history->created_at,
                'tone' => $history->event === 'document_uploaded' ? 'upload' : 'history',
            ];
        });
        $legacyUploadItems = $documents
            ->reject(fn (EclipFeaDocument $document) => $historyDocumentIds->contains($document->id))
            ->map(fn (EclipFeaDocument $document) => [
                'title' => $document->typeLabel().' uploaded',
                'detail' => collect([$document->uploader?->name, 'Secure FEA record'])->filter()->join(' · '),
                'occurred_at' => $document->created_at,
                'tone' => 'upload',
            ]);

        return $historyItems->concat($legacyUploadItems)->sortByDesc('occurred_at')->take(12)->values();
    }

    private function lastUpdated(EclipCase $case, $activity, Collection $documents)
    {
        return collect([$case->updated_at, $activity->updated_at, $documents->max('updated_at')])
            ->filter()
            ->sortDesc()
            ->first();
    }
}
