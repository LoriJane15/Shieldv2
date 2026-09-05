<?php

namespace App\Models;

use App\Enums\JapicCertificationHistoryEvent;
use App\Enums\JapicCertificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class JapicCertificationHistory extends Model
{
    protected $guarded = [
        'id',
        'japic_certification_processing_id',
        'user_id',
        'from_status',
        'to_status',
        'event',
        'japic_certification_document_version_id',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => JapicCertificationStatus::class,
            'to_status' => JapicCertificationStatus::class,
            'event' => JapicCertificationHistoryEvent::class,
            'remarks' => 'encrypted',
            'delay_reason' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('JAPIC certification history entries are immutable.'));
        static::deleting(fn () => throw new LogicException('JAPIC certification history entries are immutable.'));
    }

    public function processing(): BelongsTo
    {
        return $this->belongsTo(JapicCertificationProcessing::class, 'japic_certification_processing_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(JapicCertificationDocumentVersion::class, 'japic_certification_document_version_id');
    }
}
