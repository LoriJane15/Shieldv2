<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipDocumentVersion extends Model
{
    protected $fillable = [
        'eclip_document_id', 'version_number', 'storage_path', 'original_name',
        'mime_type', 'size_bytes', 'sha256', 'classification', 'status', 'uploaded_by', 'submitted_at',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(EclipDocument::class, 'eclip_document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
