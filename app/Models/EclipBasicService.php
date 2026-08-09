<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EclipBasicService extends Model
{
    public const STATUSES = ['pending', 'referred', 'in_progress', 'completed', 'not_applicable'];

    protected $fillable = ['eclip_case_id', 'service_type', 'gov_agency_id', 'status', 'referral_date', 'target_completion_date', 'completed_at', 'remarks', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['referral_date' => 'date', 'target_completion_date' => 'date', 'completed_at' => 'datetime'];
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(GovAgency::class, 'gov_agency_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EclipBasicServiceHistory::class, 'basic_service_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EclipBasicServiceDocument::class, 'basic_service_id');
    }

    public function isOverdue(): bool
    {
        return ! in_array($this->status, ['completed', 'not_applicable'], true)
            && $this->target_completion_date?->isBefore(today());
    }

    public function targetDateDifferenceInDays(): ?int
    {
        if (! $this->target_completion_date || in_array($this->status, ['completed', 'not_applicable'], true)) {
            return null;
        }

        return today()->diffInDays($this->target_completion_date, false);
    }
}
