<?php

namespace App\Services;

use App\Enums\Ib39FeaDocumentStatus;
use App\Enums\Ib39FeaDocumentType;
use App\Enums\Ib39FeaUploadSlot;
use App\Models\AuditLog;
use App\Models\Ib39FeaDocument;
use App\Models\Ib39FeaDocumentVersion;
use App\Models\Ib39FeaProcessing;
use App\Models\User;
use App\Support\Ib39FeaUploadedFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Ib39FeaUploadService
{
    public function __construct(private readonly Ib39FeaDocumentWorkflowService $workflow) {}

    public function store(
        Ib39FeaProcessing $processing,
        Ib39FeaDocument $document,
        Ib39FeaUploadSlot $slot,
        UploadedFile $file,
        ?int $expectedCurrentVersionId,
        ?string $replacementReason,
        User $actor,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Ib39FeaDocumentVersion {
        $photo = $this->isPhoto($document, $slot);
        $metadata = Ib39FeaUploadedFile::inspect($file, $photo);
        $path = sprintf('ib39/fea/%d/drafts/%s/%s.%s', $processing->id, $slot->value, Str::uuid(), $metadata['extension']);

        if (! Storage::disk('local')->putFileAs(dirname($path), $file, basename($path))) {
            throw new RuntimeException('The draft file could not be stored.');
        }

        try {
            $result = DB::transaction(function () use ($processing, $document, $slot, $expectedCurrentVersionId, $replacementReason, $actor, $metadata, $path, $ipAddress, $userAgent) {
                $lockedProcessing = Ib39FeaProcessing::query()->lockForUpdate()->findOrFail($processing->id);
                abort_unless($actor->is_active && $actor->hasRole('39th_ib'), 403);
                abort_unless((bool) $lockedProcessing->surfacedFormerRebel()->value('possessed_firearms'), 403);
                $lockedDocument = Ib39FeaDocument::query()->where('fea_processing_id', $lockedProcessing->id)->lockForUpdate()->findOrFail($document->id);
                abort_unless($this->slotAllowed($lockedDocument, $slot), 403);

                if ($lockedDocument->status === Ib39FeaDocumentStatus::Completed) {
                    throw ValidationException::withMessages(['file' => 'Completed documents cannot receive draft uploads.']);
                }

                $pointer = $slot === Ib39FeaUploadSlot::Primary ? 'current_draft_version_id' : 'current_supporting_photo_version_id';
                $currentId = $lockedDocument->{$pointer};
                if ($currentId !== $expectedCurrentVersionId) {
                    throw ValidationException::withMessages([
                        'expected_current_version_id' => 'This draft file was changed by another user. Reload the workspace before replacing it.',
                    ]);
                }
                $current = $currentId ? Ib39FeaDocumentVersion::query()->where('fea_document_id', $lockedDocument->id)->where('slot', $slot)->lockForUpdate()->findOrFail($currentId) : null;
                $reason = is_string($replacementReason) ? trim($replacementReason) : null;
                if ($current && blank($reason)) {
                    throw ValidationException::withMessages(['replacement_reason' => 'A replacement reason is required.']);
                }
                if (! $current && filled($reason)) {
                    throw ValidationException::withMessages(['replacement_reason' => 'A replacement reason is only accepted when replacing an existing version.']);
                }
                if ($current && hash_equals($current->sha256, $metadata['sha256'])) {
                    return ['version' => $current, 'duplicate' => true];
                }

                if ($lockedDocument->status === Ib39FeaDocumentStatus::Pending) {
                    $this->workflow->start($lockedProcessing, $lockedDocument, $actor);
                    $lockedDocument->refresh();
                }

                $versionNumber = ((int) $lockedDocument->versions()->where('slot', $slot)->max('version_number')) + 1;
                $version = $lockedDocument->versions()->create([
                    'fea_processing_id' => $lockedProcessing->id,
                    'slot' => $slot,
                    'version_number' => $versionNumber,
                    'replaces_version_id' => $current?->id,
                    'replacement_reason' => $reason,
                    'storage_path' => $path,
                    'original_filename' => $metadata['original_filename'],
                    'mime_type' => $metadata['mime_type'],
                    'size_bytes' => $metadata['size_bytes'],
                    'sha256' => $metadata['sha256'],
                    'uploaded_by' => $actor->id,
                ]);
                $lockedDocument->update([$pointer => $version->id, 'last_updated_by' => $actor->id]);
                $lockedDocument->uploadHistories()->create([
                    'fea_processing_id' => $lockedProcessing->id,
                    'fea_document_version_id' => $version->id,
                    'user_id' => $actor->id,
                    'event' => $current ? 'draft_replaced' : 'draft_uploaded',
                    'slot' => $slot,
                    'version_number' => $versionNumber,
                ]);
                $this->audit($version, $actor, $current ? 'ib39_fea_draft_file_replaced' : 'ib39_fea_draft_file_uploaded', $ipAddress, $userAgent);

                return ['version' => $version, 'duplicate' => false];
            }, 5);

            if ($result['duplicate']) {
                Storage::disk('local')->delete($path);
            }

            return $result['version'];
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    public function preview(Ib39FeaDocumentVersion $version, User $actor, ?string $ip, ?string $agent): StreamedResponse
    {
        return $this->respond($version, $actor, 'inline', 'preview', $ip, $agent);
    }

    public function download(Ib39FeaDocumentVersion $version, User $actor, ?string $ip, ?string $agent): StreamedResponse
    {
        return $this->respond($version, $actor, 'attachment', 'download', $ip, $agent);
    }

    private function respond(Ib39FeaDocumentVersion $version, User $actor, string $disposition, string $access, ?string $ip, ?string $agent): StreamedResponse
    {
        abort_unless(str_starts_with($version->storage_path, 'ib39/fea/'), 404);
        abort_unless(in_array($version->mime_type, ['application/pdf', 'image/jpeg', 'image/png'], true), 404);
        abort_unless(Storage::disk('local')->exists($version->storage_path), 404);
        $this->audit($version, $actor, 'ib39_fea_draft_file_'.$access, $ip, $agent);

        return Storage::disk('local')->response($version->storage_path, $version->original_filename, [
            'Content-Type' => $version->mime_type,
            'Content-Disposition' => $disposition.'; filename="'.addcslashes($version->original_filename, '"\\').'"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; frame-ancestors 'self'; sandbox",
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);
    }

    private function isPhoto(Ib39FeaDocument $document, Ib39FeaUploadSlot $slot): bool
    {
        return $slot === Ib39FeaUploadSlot::JustificationComparison
            || in_array($document->document_type, [Ib39FeaDocumentType::FirearmPhoto, Ib39FeaDocumentType::FrWithFirearmPhoto], true);
    }

    private function slotAllowed(Ib39FeaDocument $document, Ib39FeaUploadSlot $slot): bool
    {
        return $slot === Ib39FeaUploadSlot::Primary
            || $document->document_type === Ib39FeaDocumentType::Justification;
    }

    private function audit(Ib39FeaDocumentVersion $version, User $actor, string $action, ?string $ip, ?string $agent): void
    {
        AuditLog::query()->create([
            'user_id' => $actor->id,
            'action' => $action,
            'entity_type' => Ib39FeaDocumentVersion::class,
            'entity_id' => $version->id,
            'previous_values' => null,
            'new_values' => ['document_type' => $version->document->document_type->value, 'slot' => $version->slot->value, 'version_number' => $version->version_number],
            'ip_address' => $ip,
            'user_agent' => $agent ? mb_substr($agent, 0, 1000) : null,
        ]);
    }
}
