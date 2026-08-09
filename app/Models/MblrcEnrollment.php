<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MblrcEnrollment extends Model
{
    public const STATUSES = ['in_progress', 'completed'];

    protected $fillable = [
        'former_rebel_id', 'assigned_user_id', 'created_by', 'status',
        'integration_started_at', 'integration_completed_at', 'verified_municipality_id',
        'location_verification_remarks', 'phase_one_evidence',
    ];

    protected function casts(): array
    {
        return [
            'integration_started_at' => 'date',
            'integration_completed_at' => 'date',
            'phase_one_evidence' => 'array',
        ];
    }

    public function formerRebel(): BelongsTo
    {
        return $this->belongsTo(FormerRebel::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifiedMunicipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'verified_municipality_id');
    }

    public function referral(): HasOne
    {
        return $this->hasOne(LswdoReferral::class);
    }
}
