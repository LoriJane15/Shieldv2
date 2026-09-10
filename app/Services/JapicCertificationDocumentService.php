<?php

namespace App\Services;

use App\Enums\JapicCertificationEvent;
use App\Enums\JapicCertificationStatus;
use App\Models\Ib39CdrPhotoVersion;
use App\Models\JapicCertificationPhotoVersion;
use App\Models\JapicCertificationProcessing;
use App\Support\JapicCertificationDraftSchema;
use Illuminate\Support\Facades\Storage;

class JapicCertificationDocumentService
{
    public function __construct(private readonly JapicCertificationDraftSchema $schema) {}

    public function data(JapicCertificationProcessing $processing): array
    {
        $processing->loadMissing(['draft', 'draftHistories', 'surfacedFormerRebel.cancellation', 'triggeringCdrDocumentVersion']);
        abort_unless($processing->draft, 404, 'No certification draft is available.');
        $storedPayload = $processing->draft->payload;
        $revision = $processing->draft->revision;
        $event = $processing->histories()->where('event', JapicCertificationEvent::MarkedForSigning->value)->latest('occurred_at')->first();
        $requiresFrozen = in_array($processing->status, [JapicCertificationStatus::ForSigning, JapicCertificationStatus::AwaitingFinalUpload, JapicCertificationStatus::Completed], true);
        if ($requiresFrozen || ($processing->status === JapicCertificationStatus::Cancelled && $event)) {
            abort_unless($event, 409, 'The frozen signing revision is unavailable.');
            $revision = (int) data_get($event->metadata, 'revision');
            $history = $processing->draftHistories->firstWhere('revision', $revision);
            abort_unless($history && hash_equals((string) data_get($event->metadata, 'payload_fingerprint'), $this->schema->fingerprint($history->payload)), 409, 'The frozen signing revision is inconsistent.');
            $storedPayload = $history->payload;
        }
        $payload = $this->schema->forReading($storedPayload, $processing->control_number);

        return [
            'processing' => $processing,
            'payload' => $payload,
            'revision' => $revision,
            'purpose' => JapicCertificationDraftSchema::PURPOSE,
            'copyFurnished' => JapicCertificationDraftSchema::COPY_FURNISHED,
            'photoDataUri' => $this->photoDataUri($processing, $payload),
        ];
    }

    private function photoDataUri(JapicCertificationProcessing $processing, array $payload): ?string
    {
        $selected = data_get($payload, 'certificate.photo_version_id');
        if ($selected) {
            $photo = JapicCertificationPhotoVersion::query()->where('processing_id', $processing->id)->find((int) $selected);
            abort_unless($photo, 409, 'The selected certification photograph is unavailable.');

            return $this->dataUri($photo->getRawOriginal('storage_path'), $photo->mime_type, 'selected certification');
        }

        $sourceId = data_get($payload, 'source_snapshot.subject_photo_version_id');
        if (! $sourceId) {
            return null;
        }
        $cdrId = $processing->triggeringCdrDocumentVersion?->cdr_processing_id;
        $photo = Ib39CdrPhotoVersion::query()->whereKey((int) $sourceId)
            ->whereHas('photo', fn ($query) => $query->where('cdr_processing_id', $cdrId))->first();
        abort_unless($photo, 409, 'The authoritative subject photograph is unavailable.');

        return $this->dataUri($photo->getRawOriginal('storage_path'), $photo->mime_type, 'authoritative subject');
    }

    private function dataUri(mixed $path, mixed $mimeType, string $label): string
    {
        abort_unless(is_string($path) && is_string($mimeType) && in_array($mimeType, ['image/jpeg', 'image/png'], true), 409, "The {$label} photograph is unavailable.");
        abort_unless(Storage::disk('local')->exists($path), 404, "The {$label} photograph is unavailable.");

        return 'data:'.$mimeType.';base64,'.base64_encode(Storage::disk('local')->get($path));
    }
}
