<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipDilgReview extends Model
{
    protected $fillable = [
        'eclip_case_id', 'assistance_request_id', 'assistance_revision_id',
        'reviewed_by', 'review_level', 'decision', 'feedback', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(EclipAssistanceRevision::class, 'assistance_revision_id');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('DILG review decisions are immutable.'));
        static::deleting(fn () => throw new \LogicException('DILG review decisions are immutable.'));
    }
}
