<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LswdoReferral extends Model
{
    public const STATUSES = ['pending', 'accepted', 'returned', 'declined'];

    protected $fillable = [
        'referral_number', 'mblrc_enrollment_id', 'former_rebel_id', 'municipality_id',
        'assigned_to', 'created_by', 'status', 'referred_at', 'accepted_at', 'accepted_by', 'remarks',
    ];

    protected function casts(): array
    {
        return ['referred_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(MblrcEnrollment::class, 'mblrc_enrollment_id');
    }

    public function formerRebel(): BelongsTo
    {
        return $this->belongsTo(FormerRebel::class);
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accepter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function eclipCase(): HasOne
    {
        return $this->hasOne(EclipCase::class);
    }
}
