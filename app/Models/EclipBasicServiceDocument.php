<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipBasicServiceDocument extends Model
{
    protected $fillable = ['basic_service_id', 'version_number', 'storage_path', 'original_name', 'mime_type', 'size_bytes', 'sha256', 'uploaded_by'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(EclipBasicService::class, 'basic_service_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
