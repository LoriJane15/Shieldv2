<?php

namespace App\Models;

use App\Enums\JapicCertificationStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JapicCertificationProcessing extends Model
{
    protected $guarded = ['id', 'ib39_surfaced_former_rebel_id'];

    protected $hidden = ['control_number'];

    protected function casts(): array
    {
        return [
            'status' => JapicCertificationStatus::class,
            'received_at' => 'immutable_datetime',
            'due_at' => 'immutable_datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'control_number' => 'encrypted',
            'lock_version' => 'integer',
        ];
    }

    protected function delayed(): Attribute
    {
        return Attribute::get(fn (): bool => $this->status->isActive() && now()->greaterThan($this->due_at));
    }

    public function surfacedFormerRebel(): BelongsTo
    {
        return $this->belongsTo(Ib39SurfacedFormerRebel::class, 'ib39_surfaced_former_rebel_id');
    }

    public function triggeringCdrDocumentVersion(): BelongsTo
    {
        return $this->belongsTo(Ib39CdrDocumentVersion::class, 'triggering_cdr_document_version_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function draft(): HasOne
    {
        return $this->hasOne(JapicCertificationDraft::class, 'processing_id');
    }

    public function draftHistories(): HasMany
    {
        return $this->hasMany(JapicCertificationDraftHistory::class, 'processing_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(JapicCertificationHistory::class, 'processing_id');
    }

    public function documentVersions(): HasMany
    {
        return $this->hasMany(JapicCertificationDocumentVersion::class, 'processing_id');
    }

    public function currentFinalVersion(): BelongsTo
    {
        return $this->belongsTo(JapicCertificationDocumentVersion::class, 'current_final_version_id');
    }
}
