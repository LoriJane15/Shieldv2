<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipAuthenticationHistory extends Model
{
    protected $fillable = ['user_id', 'from_status', 'to_status', 'remarks', 'data', 'ip_address'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(EclipAuthenticationRequest::class, 'authentication_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
