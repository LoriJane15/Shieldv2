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
            'afp' => 'afp.dashboard',
            default => 'login',
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
}
