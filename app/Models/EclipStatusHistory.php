<?php

namespace App\Models;

use App\Enums\EclipCaseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipStatusHistory extends Model
{
    protected $fillable = ['eclip_case_id', 'user_id', 'from_status', 'to_status', 'remarks', 'ip_address'];

    protected function casts(): array
    {
        return [
            'from_status' => EclipCaseStatus::class,
            'to_status' => EclipCaseStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Case status history entries are immutable.'));
        static::deleting(fn () => throw new \LogicException('Case status history entries are immutable.'));
    }
}
