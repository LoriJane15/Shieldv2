<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipLivelihoodBeneficiaryAssistanceHistory extends Model
{
    protected $fillable = ['user_id', 'action', 'previous_values', 'new_values', 'ip_address'];

    protected function casts(): array
    {
        return ['previous_values' => 'array', 'new_values' => 'array'];
    }

    public function assistance(): BelongsTo
    {
        return $this->belongsTo(EclipLivelihoodBeneficiaryAssistance::class, 'assistance_id');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Livelihood-beneficiary assistance history entries are immutable.'));
        static::deleting(fn () => throw new \LogicException('Livelihood-beneficiary assistance history entries are immutable.'));
    }
}
