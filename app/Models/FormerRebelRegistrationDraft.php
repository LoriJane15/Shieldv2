<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormerRebelRegistrationDraft extends Model
{
    protected $fillable = ['user_id', 'payload', 'saved_at'];

    protected function casts(): array
    {
        return [
            'payload' => 'encrypted:array',
            'saved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
