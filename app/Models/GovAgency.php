<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class GovAgency extends Model
{
    protected $fillable = ['name', 'acronym', 'profile'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AgencyImplanResponse::class);
    }

    public function taggings(): HasMany
    {
        return $this->hasMany(ImplementationTagging::class);
    }

    public function eclipBasicServices(): HasMany
    {
        return $this->hasMany(EclipBasicService::class);
    }

    public function getProfileUrlAttribute(): string
    {
        if (! $this->profile) {
            return asset('assets/img/kc-logo.svg');
        }

        if (Storage::disk('public')->exists($this->profile)) {
            return Storage::disk('public')->url($this->profile);
        }

        return asset('assets/logoAgency/'.ltrim($this->profile, '/'));
    }
}
