<?php

namespace App\Services;

use App\Models\EclipCase;
use App\Models\EclipDocument;
use App\Models\EclipDocumentRequirement;
use App\Models\EclipDocumentVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EclipDocumentService
{
    public function __construct(
        private readonly EclipDocumentStorageService $storage,
        private readonly EclipCaseWorkflowService $workflow,
    ) {}

    public function upload(
        EclipCase $case,
        EclipDocumentRequirement $requirement,
        UploadedFile $file,
        User $actor,
        ?string $ipAddress,
    ): EclipDocumentVersion {
        if (! $requirement->is_active) {
            throw ValidationException::withMessages(['document' => 'This document requirement is not active.']);
        }

        $document = EclipDocument::query()->firstOrCreate([
            'eclip_case_id' => $case->id,
            'requirement_id' => $requirement->id,
        ], ['status' => 'pending']);
        $checksum = hash_file('sha256', $file->getRealPath());
        $safeOriginalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $safeOriginalName = preg_replace('/[^\pL\pN._ -]/u', '_', $safeOriginalName)
            ?: 'document.'.strtolower($file->extension() ?: 'bin');
        $path = $this->storage->store($file, $document);

        try {
            return DB::transaction(function () use ($case, $document, $file, $path, $checksum, $safeOriginalName, $actor, $ipAddress) {
                $lockedDocument = EclipDocument::query()->lockForUpdate()->findOrFail($document->id);
                $nextVersion = ((int) $lockedDocument->versions()->max('version_number')) + 1;
                $version = $lockedDocument->versions()->create([
                    'version_number' => $nextVersion,
                    'storage_path' => $path,
                    'original_name' => $safeOriginalName,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                    'sha256' => $checksum,
                    'uploaded_by' => $actor->id,
                ]);
                $lockedDocument->update(['status' => 'pending']);
                $this->workflow->beginDocumentProcessing($case, $actor, $ipAddress);

                return $version;
            });
        } catch (\Throwable $exception) {
            $this->storage->delete($path);
            throw $exception;
        }
    }

    public function review(
        EclipDocument $document,
        User $actor,
        string $decision,
        ?string $remarks,
        ?string $ipAddress,
    ): void {
        if ($decision === 'invalid' && blank($remarks)) {
            throw ValidationException::withMessages(['remarks' => 'Remarks are required when a document is invalid.']);
        }

        DB::transaction(function () use ($document, $actor, $decision, $remarks, $ipAddress) {
            $lockedDocument = EclipDocument::query()->lockForUpdate()->findOrFail($document->id);
            $version = $lockedDocument->versions()->latest('version_number')->firstOrFail();
            $allowed = match ($decision) {
                'authenticated' => $lockedDocument->status === 'pending',
                'certified' => $lockedDocument->status === 'authenticated',
                'invalid' => in_array($lockedDocument->status, ['pending', 'authenticated'], true),
                default => false,
            };

            if (! $allowed) {
                throw ValidationException::withMessages(['decision' => 'This review decision is not valid for the current document status.']);
            }

            $lockedDocument->reviews()->create([
                'document_version_id' => $version->id,
                'reviewed_by' => $actor->id,
                'decision' => $decision,
                'remarks' => $remarks,
                'reviewed_at' => now(),
            ]);
            $lockedDocument->update(['status' => $decision]);
            $case = $lockedDocument->eclipCase()->firstOrFail();

            if ($decision === 'invalid') {
                $this->workflow->markDocumentsIncomplete($case, $actor, $remarks, $ipAddress);
            } elseif ($decision === 'certified' && $this->allRequiredDocumentsCertified($case)) {
                $this->workflow->markDocumentsCertified($case, $actor, $ipAddress);
            }
        });
    }

    private function allRequiredDocumentsCertified(EclipCase $case): bool
    {
        $required = EclipDocumentRequirement::query()->where('is_active', true)->where('is_required', true);

        return $required->exists() && ! (clone $required)->whereDoesntHave('documents', fn ($query) => $query
            ->where('eclip_case_id', $case->id)
            ->where('status', 'certified'))->exists();
    }
}
