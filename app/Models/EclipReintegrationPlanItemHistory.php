<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipReintegrationPlanItemHistory extends Model
{
    protected $fillable = ['user_id', 'action', 'previous_values', 'new_values', 'ip_address'];

    protected function casts(): array
    {
        return ['previous_values' => 'array', 'new_values' => 'array'];
    }

    public function planItem(): BelongsTo
    {
        return $this->belongsTo(EclipReintegrationPlanItem::class, 'plan_item_id');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Reintegration plan history entries are immutable.'));
        static::deleting(fn () => throw new \LogicException('Reintegration plan history entries are immutable.'));
    }
}
