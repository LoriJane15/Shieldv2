<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipFeaDocument extends Model
{
    public const TYPES = ['ptis', 'tir', 'cvif', 'other'];

    public const REQUIRED_TYPES = ['ptis', 'tir', 'cvif'];

    public const TYPE_LABELS = [
        'ptis' => 'Property Turn-In Slip (PTIS)',
        'tir' => 'Technical Inspection Report (TIR)',
        'cvif' => 'Cost Valuation of Inventory Firearms (CVIF)',
        'other' => 'Other Authorized FEA Record',
    ];

    protected $fillable = [
        'document_type', 'storage_path', 'original_name', 'mime_type', 'size_bytes', 'sha256', 'uploaded_by',
    ];

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->document_type] ?? str($this->document_type)->replace('_', ' ')->title()->toString();
    }
}
