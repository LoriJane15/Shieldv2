<?php

namespace App\Services;

use App\Enums\JapicCertificationEvent;
use App\Enums\JapicCertificationStatus;
use App\Models\JapicCertificationDraft;
use App\Models\JapicCertificationProcessing;
use App\Models\User;
use App\Support\JapicCertificationDraftSchema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class JapicCertificationDraftService
{
    public function __construct(private readonly JapicCertificationDraftSchema $schema) {}

    public function save(JapicCertificationProcessing $processing, array $manual, string $controlNumber, int $expectedRevision, int $expectedLockVersion, ?string $delayReason, User $actor): JapicCertificationDraft
    {
        return DB::transaction(function () use ($processing, $manual, $controlNumber, $expectedRevision, $expectedLockVersion, $delayReason, $actor) {
            $locked = JapicCertificationProcessing::query()->lockForUpdate()->findOrFail($processing->id);
            $this->assertActor($locked, $actor);
            if (! in_array($locked->status, [JapicCertificationStatus::Pending, JapicCertificationStatus::Drafting], true)) {
                throw ValidationException::withMessages(['draft' => 'This certification is not available for editing.']);
            }
            if ($locked->lock_version !== $expectedLockVersion) {
                throw new ConflictHttpException('A newer certification change exists. Reload before saving.');
            }
            $draft = JapicCertificationDraft::query()->where('processing_id', $locked->id)->lockForUpdate()->first();
            if (($draft?->revision ?? 0) !== $expectedRevision) {
                throw new ConflictHttpException('A newer draft revision exists. Reload before saving.');
            }
            $this->assertDelay($locked, $delayReason);
            $payload = $this->schema->normalize($manual, $this->schema->sourceSnapshot($locked));
            $controlNumber = trim($controlNumber);
            $controlHash = $this->schema->controlNumberHash($controlNumber);
            if (JapicCertificationProcessing::query()->where('control_number_hash', $controlHash)->whereKeyNot($locked->id)->exists()) {
                throw ValidationException::withMessages(['control_number' => 'This control number is unavailable.']);
            }
            $unchanged = $draft && hash_equals($this->schema->fingerprint($draft->payload), $this->schema->fingerprint($payload))
                && hash_equals((string) $locked->control_number_hash, $controlHash);
            if ($unchanged) {
                return $draft;
            }
            $revision = $expectedRevision + 1;
            $now = now();
            try {
                $draft ??= new JapicCertificationDraft;
                $draft->forceFill(['processing_id' => $locked->id, 'payload' => $payload, 'schema_version' => JapicCertificationDraftSchema::VERSION,
                    'revision' => $revision, 'last_saved_by' => $actor->id, 'last_saved_at' => $now])->save();
                $locked->draftHistories()->create(['revision' => $revision, 'payload' => $payload, 'saved_by' => $actor->id, 'saved_at' => $now]);
                $from = $locked->status;
                $locked->forceFill(['status' => JapicCertificationStatus::Drafting, 'started_at' => $locked->started_at ?? $now,
                    'started_by' => $locked->started_by ?? $actor->id, 'control_number' => $controlNumber,
                    'control_number_hash' => $controlHash, 'lock_version' => $locked->lock_version + 1])->save();
                $locked->histories()->create(['actor_id' => $actor->id, 'from_status' => $from, 'to_status' => JapicCertificationStatus::Drafting,
                    'event' => $from === JapicCertificationStatus::Pending ? JapicCertificationEvent::Started : JapicCertificationEvent::DraftSaved,
                    'delay_reason' => $delayReason, 'metadata' => ['revision' => $revision, 'schema_version' => JapicCertificationDraftSchema::VERSION,
                        'payload_fingerprint' => $this->schema->fingerprint($payload)], 'occurred_at' => $now]);
            } catch (QueryException $exception) {
                if (str_contains(strtolower($exception->getMessage()), 'control')) {
                    throw ValidationException::withMessages(['control_number' => 'This control number is unavailable.']);
                }
                throw $exception;
            }

            return $draft->fresh();
        }, 5);
    }

    private function assertActor(JapicCertificationProcessing $processing, User $actor): void
    {
        abort_unless($actor->is_active && $actor->hasRole('japic') && ($processing->assigned_to === null || $processing->assigned_to === $actor->id), 403);
        abort_if($processing->surfacedFormerRebel()->whereHas('cancellation')->exists(), 403, 'Cancelled certifications are read-only.');
    }

    private function assertDelay(JapicCertificationProcessing $processing, ?string $reason): void
    {
        if ($processing->due_at->copy()->startOfDay()->lt(now()->startOfDay()) && blank($reason)) {
            throw ValidationException::withMessages(['delay_reason' => 'A delay reason is required for an overdue certification.']);
        }
    }
}
