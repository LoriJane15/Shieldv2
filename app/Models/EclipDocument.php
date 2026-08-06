<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EclipDocument extends Model
{
    protected $fillable = ['eclip_case_id', 'requirement_id', 'status'];

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(EclipDocumentRequirement::class, 'requirement_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(EclipDocumentVersion::class);
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(EclipDocumentVersion::class)->ofMany('version_number', 'max');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(EclipDocumentReview::class);
    }
}
