<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EclipAuthenticationRequest extends Model
{
    protected $fillable = [
        'eclip_case_id', 'requested_by', 'assigned_to', 'status', 'requested_at',
        'started_at', 'due_at', 'decided_at', 'certification_reference', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime', 'started_at' => 'datetime',
            'due_at' => 'datetime', 'decided_at' => 'datetime',
        ];
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EclipAuthenticationHistory::class, 'authentication_request_id');
    }
}
