<?php

namespace App\Models;

use App\Enums\Ib39FeaComplianceStatus;
use App\Enums\Ib39FeaDocumentStatus;
use App\Enums\Ib39FeaDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ib39FeaDocument extends Model
{
    protected $fillable = [
        'document_type', 'status', 'compliance_status', 'is_required',
        'started_at', 'completed_at', 'remarks', 'delay_reason',
        'prepared_by', 'last_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => Ib39FeaDocumentType::class,
            'status' => Ib39FeaDocumentStatus::class,
            'compliance_status' => Ib39FeaComplianceStatus::class,
            'is_required' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'remarks' => 'encrypted',
            'delay_reason' => 'encrypted',
        ];
    }

    public function processing(): BelongsTo
    {
        return $this->belongsTo(Ib39FeaProcessing::class, 'fea_processing_id');
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function lastUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}
