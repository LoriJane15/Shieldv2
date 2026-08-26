<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipWorkflowDocument extends Model
{
    protected $fillable = [
        'uploaded_by', 'document_type', 'version_number', 'storage_path', 'original_name',
        'mime_type', 'size_bytes', 'sha256', 'remarks',
    ];

    protected function casts(): array
    {
        return ['version_number' => 'integer', 'size_bytes' => 'integer'];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(EclipWorkflowActivity::class, 'activity_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
