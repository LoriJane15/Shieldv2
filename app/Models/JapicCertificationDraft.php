<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JapicCertificationDraft extends Model
{
    protected $guarded = [
        'id',
        'japic_certification_processing_id',
        'content',
        'schema_version',
        'revision',
        'fr_photo_version_id',
        'last_saved_by',
        'saved_at',
    ];

    protected $hidden = ['content'];

    protected function casts(): array
    {
        return [
            'content' => 'encrypted:array',
            'schema_version' => 'integer',
            'revision' => 'integer',
            'saved_at' => 'datetime',
        ];
    }

    public function processing(): BelongsTo
    {
        return $this->belongsTo(JapicCertificationProcessing::class, 'japic_certification_processing_id');
    }

    public function frPhotoVersion(): BelongsTo
    {
        return $this->belongsTo(Ib39CdrPhotoVersion::class, 'fr_photo_version_id');
    }

    public function lastSavedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_saved_by');
    }
}
