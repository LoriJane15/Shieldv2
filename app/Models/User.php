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
            'lswdo' => 'lswdo.eclip.index',
            'japic' => 'japic.eclip.index',
            'eclip_assessor' => 'eclip_assessor.cases.index', // legacy compatibility
            'dilg_reviewer' => 'dilg_reviewer.cases.index', // legacy compatibility
            'eclip_funding_officer' => 'eclip_funding.cases.index', // legacy compatibility
            'dilg_provincial_focal', 'dilg_regional', 'nboo_eclip_pmo' => 'dilg_reviewer.cases.index',
            'dilg_fms' => 'eclip_funding.cases.index',
            'pnp' => 'pnp.dashboard',
            'local_eclip_committee' => 'local_eclip.cases.index',
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

    public function createdEclipCases(): HasMany
    {
        return $this->hasMany(EclipCase::class, 'created_by');
    }

    public function assignedEclipCases(): HasMany
    {
        return $this->hasMany(EclipCase::class, 'assigned_to');
    }

    public function eclipEligibilityReviews(): HasMany
    {
        return $this->hasMany(EclipEligibilityReview::class, 'reviewed_by');
    }

    public function eclipStatusHistories(): HasMany
    {
        return $this->hasMany(EclipStatusHistory::class);
    }

    public function eclipDocumentVersions(): HasMany
    {
        return $this->hasMany(EclipDocumentVersion::class, 'uploaded_by');
    }

    public function eclipDocumentReviews(): HasMany
    {
        return $this->hasMany(EclipDocumentReview::class, 'reviewed_by');
    }

    public function eclipDocumentRequirementHistories(): HasMany
    {
        return $this->hasMany(EclipDocumentRequirementHistory::class);
    }

    public function eclipAssistanceRevisions(): HasMany
    {
        return $this->hasMany(EclipAssistanceRevision::class, 'created_by');
    }

    public function eclipAssistanceRequests(): HasMany
    {
        return $this->hasMany(EclipAssistanceRequest::class, 'created_by');
    }

    public function eclipAssistanceCategoryHistories(): HasMany
    {
        return $this->hasMany(EclipAssistanceCategoryHistory::class);
    }

    public function eclipDilgReviews(): HasMany
    {
        return $this->hasMany(EclipDilgReview::class, 'reviewed_by');
    }

    public function eclipFundTransactions(): HasMany
    {
        return $this->hasMany(EclipFundTransaction::class, 'created_by');
    }

    public function eclipAssistanceReleases(): HasMany
    {
        return $this->hasMany(EclipAssistanceRelease::class, 'released_by');
    }

    public function eclipReportExports(): HasMany
    {
        return $this->hasMany(EclipReportExport::class);
    }

    public function canViewEclipAnalytics(): bool
    {
        return in_array($this->role, config('shield.eclip_analytics_roles', []), true)
            && ($this->hasRole('admin', 'super_admin', 'dilg_regional', 'nboo_eclip_pmo', 'dilg_fms')
                || $this->municipality_id !== null);
    }

    public function canViewEclipFinancialAnalytics(): bool
    {
        return $this->canViewEclipAnalytics()
            && in_array($this->role, config('shield.eclip_financial_analytics_roles', []), true);
    }
}
