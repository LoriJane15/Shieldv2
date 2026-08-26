<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipEligibilityReview extends Model
{
    protected $fillable = [
        'eclip_case_id', 'reviewed_by', 'decision', 'referral_status',
        'referred_program', 'remarks', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Eligibility decisions are immutable.'));
        static::deleting(fn () => throw new \LogicException('Eligibility decisions are immutable.'));
    }
}
