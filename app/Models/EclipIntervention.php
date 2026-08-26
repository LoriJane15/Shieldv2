<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EclipIntervention extends Model
{
    public const STAGES = ['social_protection', 'reintegration'];

    public const STATUSES = ['pending', 'referred', 'in_progress', 'completed', 'returned', 'not_applicable'];

    protected $fillable = ['stage', 'title', 'provider', 'status', 'amount_or_value', 'target_date', 'completed_at', 'outcome', 'remarks', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['amount_or_value' => 'decimal:2', 'target_date' => 'date', 'completed_at' => 'datetime'];
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EclipInterventionHistory::class, 'intervention_id');
    }
}
