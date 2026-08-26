<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipFeaDocument extends Model
{
    public const TYPES = ['ptis', 'tir', 'cvif', 'other'];

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
}
