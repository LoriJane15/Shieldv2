<?php

namespace App\Models;

use App\Enums\JapicCertificationDocumentSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class JapicCertificationDocumentVersion extends Model
{
    protected $guarded = [
        'id',
        'japic_certification_processing_id',
        'version_number',
        'source',
        'replaces_version_id',
        'storage_path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'sha256',
        'uploaded_by',
        'confirmed_at',
        'uploaded_at',
    ];

    protected $hidden = ['storage_path', 'sha256'];

    protected function casts(): array
    {
        return [
            'source' => JapicCertificationDocumentSource::class,
            'version_number' => 'integer',
            'replacement_reason' => 'encrypted',
            'size_bytes' => 'integer',
            'confirmed_correct_fr' => 'boolean',
            'confirmed_complete' => 'boolean',
            'confirmed_signatures_present' => 'boolean',
            'confirmed_final_copy' => 'boolean',
            'confirmed_at' => 'datetime',
            'uploaded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('JAPIC certification document versions are immutable.'));
        static::deleting(fn () => throw new LogicException('JAPIC certification document versions are immutable.'));
    }

    public function processing(): BelongsTo
    {
        return $this->belongsTo(JapicCertificationProcessing::class, 'japic_certification_processing_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function replacesVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_version_id');
    }

    public function replacementVersions(): HasMany
    {
        return $this->hasMany(self::class, 'replaces_version_id');
    }
}
