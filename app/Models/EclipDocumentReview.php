<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipDocumentReview extends Model
{
    protected $fillable = ['eclip_document_id', 'document_version_id', 'reviewed_by', 'decision', 'remarks', 'reviewed_at'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(EclipDocument::class, 'eclip_document_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(EclipDocumentVersion::class, 'document_version_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
