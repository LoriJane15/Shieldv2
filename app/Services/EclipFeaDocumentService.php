<?php

namespace App\Services;

use App\Models\EclipCase;
use App\Models\EclipFeaDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EclipFeaDocumentService
{
    public function __construct(private readonly EclipDocumentStorageService $storage) {}

    public function store(EclipCase $case, string $documentType, UploadedFile $file, User $actor, ?string $ipAddress): EclipFeaDocument
    {
        $path = $this->storage->storeFea($file, $case->id);

        try {
            return DB::transaction(function () use ($case, $documentType, $file, $actor, $ipAddress, $path) {
                $lockedCase = EclipCase::query()->lockForUpdate()->findOrFail($case->id);
                $activity = $lockedCase->workflowActivities()->where('step_code', '4B')->lockForUpdate()->firstOrFail();

                if (! in_array($activity->status, ['pending', 'ongoing', 'late', 'returned_for_correction'], true)) {
                    throw ValidationException::withMessages(['document' => 'This FEA case is not accepting additional documents.']);
                }

                $originalName = $this->safeOriginalName($file);
                $document = $lockedCase->feaDocuments()->create([
                    'document_type' => $documentType,
                    'storage_path' => $path,
                    'original_name' => $originalName,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                    'uploaded_by' => $actor->id,
                ]);

                $fromStatus = $activity->status;
                $toStatus = in_array($fromStatus, ['pending', 'late', 'returned_for_correction'], true) ? 'ongoing' : $fromStatus;
                if ($toStatus !== $fromStatus) {
                    $activity->update([
                        'status' => $toStatus,
                        'started_at' => $activity->started_at ?? now(),
                    ]);
                }

                $activity->histories()->create([
                    ...$this->actorContext($actor),
                    'event' => 'document_uploaded',
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'remarks' => 'Secure FEA document uploaded: '.EclipFeaDocument::TYPE_LABELS[$documentType].'.',
                    'data' => [
                        'fea_document_id' => $document->id,
                        'document_type' => $documentType,
                        'sha256' => $document->sha256,
                        'size_bytes' => $document->size_bytes,
                    ],
                    'ip_address' => $ipAddress,
                ]);

                return $document;
            }, 3);
        } catch (\Throwable $exception) {
            $this->storage->delete($path);

            throw $exception;
        }
    }

    private function safeOriginalName(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));

        return preg_replace('/[^\pL\pN._ -]/u', '_', $name) ?: 'fea-document.'.strtolower($file->extension() ?: 'bin');
    }

    private function actorContext(User $actor): array
    {
        $actor->loadMissing(['municipality', 'govAgency']);

        return [
            'user_id' => $actor->id,
            'actor_role' => $actor->role,
            'actor_office' => $actor->govAgency?->name
                ?? $actor->municipality?->name
                ?? config("shield.roles.{$actor->role}.label", str($actor->role)->replace('_', ' ')->title()->toString()),
        ];
    }
}
