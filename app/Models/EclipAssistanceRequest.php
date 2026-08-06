<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EclipAssistanceRequest extends Model
{
    protected $fillable = ['eclip_case_id', 'created_by', 'status', 'submitted_at'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(EclipAssistanceRevision::class, 'assistance_request_id');
    }

    public function latestRevision(): HasOne
    {
        return $this->hasOne(EclipAssistanceRevision::class, 'assistance_request_id')->ofMany('revision_number', 'max');
    }
}
