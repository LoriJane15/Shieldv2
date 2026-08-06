<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipAssistanceRelease extends Model
{
    protected $fillable = [
        'eclip_case_id', 'assistance_request_id', 'assistance_revision_id', 'amount',
        'release_reference', 'released_at', 'remarks', 'acknowledgment_path',
        'acknowledgment_original_name', 'acknowledgment_mime_type',
        'acknowledgment_size_bytes', 'acknowledgment_sha256', 'released_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'released_at' => 'date'];
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }
}
