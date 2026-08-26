<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EclipReintegrationPlanItem extends Model
{
    public const STATUSES = ['planned', 'referred', 'in_progress', 'completed', 'transferred', 'not_applicable'];

    protected $fillable = [
        'identified_need', 'proposed_assistance', 'responsible_agency', 'lgu_counterpart',
        'form_of_assistance', 'amount', 'target_date', 'status', 'partner_agencies',
        'agency_commitments', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'target_date' => 'date'];
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(EclipIntervention::class, 'reintegration_plan_item_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EclipReintegrationPlanItemHistory::class, 'plan_item_id');
    }
}
