<?php

namespace App\Models;

use App\Enums\EclipCaseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EclipCase extends Model
{
    protected $fillable = [
        'case_number', 'former_rebel_id', 'municipality_id', 'created_by',
        'assigned_to', 'status', 'submitted_at', 'eligibility_decided_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EclipCaseStatus::class,
            'submitted_at' => 'datetime',
            'eligibility_decided_at' => 'datetime',
        ];
    }

    public function formerRebel(): BelongsTo
    {
        return $this->belongsTo(FormerRebel::class);
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function eligibilityReviews(): HasMany
    {
        return $this->hasMany(EclipEligibilityReview::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(EclipStatusHistory::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EclipDocument::class);
    }

    public function assistanceRequest(): HasOne
    {
        return $this->hasOne(EclipAssistanceRequest::class);
    }

    public function dilgReviews(): HasMany
    {
        return $this->hasMany(EclipDilgReview::class);
    }

    public function fundTransactions(): HasMany
    {
        return $this->hasMany(EclipFundTransaction::class);
    }

    public function assistanceReleases(): HasMany
    {
        return $this->hasMany(EclipAssistanceRelease::class);
    }

    public function basicServices(): HasMany
    {
        return $this->hasMany(EclipBasicService::class);
    }
}
