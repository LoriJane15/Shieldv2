<?php

namespace App\Models;

use App\Services\AgencyLogoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class GovAgency extends Model
{
    protected $fillable = [
        'name', 'acronym', 'profile', 'logo_original_name', 'logo_mime_type', 'logo_size_bytes',
    ];

    protected function casts(): array
    {
        return ['logo_size_bytes' => 'integer'];
    }

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

    public function getProfileUrlAttribute(): string
    {
        if (! $this->profile) {
            return asset('assets/img/kc-logo.svg');
        }

        $logos = app(AgencyLogoService::class);
        if ($logos->isManagedPath($this->profile) && Storage::disk('public')->exists($this->profile)) {
            return Storage::disk('public')->url($this->profile);
        }

        if ($logos->isSafeLegacyPath($this->profile)) {
            return asset('assets/uploadLogo/'.$this->profile);
        }

        return asset('assets/img/kc-logo.svg');
    }
}
