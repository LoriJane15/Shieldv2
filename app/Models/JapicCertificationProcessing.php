<?php

namespace App\Models;

use App\Enums\JapicCertificationStatus;
use App\Enums\JapicCertificationTriggerSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JapicCertificationProcessing extends Model
{
    protected $guarded = [
        'id',
        'ib39_surfaced_former_rebel_id',
        'triggering_cdr_document_version_id',
        'status',
        'received_on',
        'due_on',
        'trigger_source',
        'started_at',
        'completed_at',
        'current_final_version_id',
        'control_number',
        'control_number_hash',
    ];

    protected $hidden = [
        'control_number_hash',
    ];

    protected function casts(): array
    {
        return [
            'status' => JapicCertificationStatus::class,
            'trigger_source' => JapicCertificationTriggerSource::class,
            'received_on' => 'date',
            'due_on' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'control_number' => 'encrypted',
            'delay_reason' => 'encrypted',
        ];
    }

    public function surfacedFormerRebel(): BelongsTo
    {
        return $this->belongsTo(Ib39SurfacedFormerRebel::class, 'ib39_surfaced_former_rebel_id');
    }

    public function triggeringCdrDocumentVersion(): BelongsTo
    {
        return $this->belongsTo(Ib39CdrDocumentVersion::class, 'triggering_cdr_document_version_id');
    }

    public function draft(): HasOne
    {
        return $this->hasOne(JapicCertificationDraft::class, 'japic_certification_processing_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(JapicCertificationHistory::class, 'japic_certification_processing_id');
    }

    public function documentVersions(): HasMany
    {
        return $this->hasMany(JapicCertificationDocumentVersion::class, 'japic_certification_processing_id');
    }

    public function currentFinalVersion(): BelongsTo
    {
        return $this->belongsTo(JapicCertificationDocumentVersion::class, 'current_final_version_id');
    }
}
