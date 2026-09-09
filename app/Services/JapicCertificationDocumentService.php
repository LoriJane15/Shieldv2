<?php

namespace App\Services;

use App\Enums\JapicCertificationEvent;
use App\Enums\JapicCertificationStatus;
use App\Models\Ib39CdrPhotoVersion;
use App\Models\JapicCertificationProcessing;
use App\Support\JapicCertificationDraftSchema;
use Illuminate\Support\Facades\Storage;

class JapicCertificationDocumentService
{
    public function __construct(private readonly JapicCertificationDraftSchema $schema) {}

    public function data(JapicCertificationProcessing $processing): array
    {
        $processing->loadMissing(['draft', 'draftHistories', 'surfacedFormerRebel.cancellation']);
        abort_unless($processing->draft, 404, 'No certification draft is available.');
        $payload = $processing->draft->payload;
        $revision = $processing->draft->revision;
        $event = $processing->histories()->where('event', JapicCertificationEvent::MarkedForSigning->value)->latest('occurred_at')->first();
        $requiresFrozen = in_array($processing->status, [JapicCertificationStatus::ForSigning, JapicCertificationStatus::AwaitingFinalUpload, JapicCertificationStatus::Completed], true);
        if ($requiresFrozen || ($processing->status === JapicCertificationStatus::Cancelled && $event)) {
            abort_unless($event, 409, 'The frozen signing revision is unavailable.');
            $revision = (int) data_get($event->metadata, 'revision');
            $history = $processing->draftHistories->firstWhere('revision', $revision);
            abort_unless($history && hash_equals((string) data_get($event->metadata, 'payload_fingerprint'), $this->schema->fingerprint($history->payload)), 409, 'The frozen signing revision is inconsistent.');
            $payload = $history->payload;
        }
        abort_unless((int) data_get($payload, 'schema_version') === JapicCertificationDraftSchema::VERSION, 409, 'The draft schema is unsupported.');

        return ['processing' => $processing, 'payload' => $payload, 'revision' => $revision,
            'positions' => JapicCertificationDraftSchema::POSITIONS, 'purpose' => JapicCertificationDraftSchema::PURPOSE,
            'photoDataUri' => $this->photoDataUri($processing, data_get($payload, 'source_snapshot.subject_photo_version_id'))];
    }

    private function photoDataUri(JapicCertificationProcessing $processing, mixed $id): ?string
    {
        if (! $id) {
            return null;
        }
        $photo = Ib39CdrPhotoVersion::query()->whereKey((int) $id)
            ->whereHas('photo', fn ($query) => $query->where('cdr_processing_id', $processing->triggeringCdrDocumentVersion->cdr_processing_id))->first();
        abort_unless($photo && in_array($photo->mime_type, ['image/jpeg', 'image/png'], true), 409, 'The authoritative subject photograph is unavailable.');
        $path = $photo->getRawOriginal('storage_path');
        abort_unless(Storage::disk('local')->exists($path), 404, 'The authoritative subject photograph is unavailable.');

        return 'data:'.$photo->mime_type.';base64,'.base64_encode(Storage::disk('local')->get($path));
    }
}
