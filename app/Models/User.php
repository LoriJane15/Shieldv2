<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'logo',
        'municipality_id',
        'gov_agency_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Role helpers --------------------------------------------------------
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /** Landing route for this user's role after login. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'super_admin' => 'super_admin.dashboard',
            'admin' => 'admin.dashboard',
            '39th_ib' => 'ib39.dashboard',
            'gov_agency' => 'gov_agency.dashboard',
            'lgu' => 'lgu.dashboard',
            'mblrc' => 'mblrc.dashboard',
            'lswdo' => 'profile.edit',
            'japic', 'eclip_assessor', 'dilg_reviewer', 'eclip_funding_officer',
            'dilg_provincial_focal', 'dilg_regional', 'nboo_eclip_pmo', 'dilg_fms',
            'local_eclip_committee' => 'profile.edit',
            'pnp' => 'profile.edit',
            'afp' => 'afp.dashboard',
            default => 'profile.edit',
        };
    }

    public function getLogoUrlAttribute(): string
    {
        if (! $this->logo) {
            return asset('assets/img/kc-logo.svg');
        }

        if (Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }

        return asset('assets/'.ltrim($this->logo, '/'));
    }

    // Relationships -------------------------------------------------------
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function govAgency(): BelongsTo
    {
        return $this->belongsTo(GovAgency::class);
    }

    public function rcspForms(): HasMany
    {
        return $this->hasMany(RcspForm::class, 'lgu_user_id');
    }

    public function implementations(): HasMany
    {
        return $this->hasMany(Implementation::class, 'lgu_user_id');
    }

    public function createdIb39SurfacedFormerRebels(): HasMany
    {
        return $this->hasMany(Ib39SurfacedFormerRebel::class, 'created_by');
    }

    public function completedIb39CdrProcessings(): HasMany
    {
        return $this->hasMany(Ib39CdrProcessing::class, 'completed_by');
    }

    public function editedIb39CdrForms(): HasMany
    {
        return $this->hasMany(Ib39CdrForm::class, 'last_edited_by');
    }

    public function createdIb39CdrDocumentVersions(): HasMany
    {
        return $this->hasMany(Ib39CdrDocumentVersion::class, 'created_by');
    }

    public function uploadedIb39CdrPhotoVersions(): HasMany
    {
        return $this->hasMany(Ib39CdrPhotoVersion::class, 'uploaded_by');
    }
}
