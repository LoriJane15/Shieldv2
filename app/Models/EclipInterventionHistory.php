<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipInterventionHistory extends Model
{
    protected $fillable = ['user_id', 'action', 'previous_values', 'new_values', 'ip_address'];

    protected function casts(): array
    {
        return ['previous_values' => 'array', 'new_values' => 'array'];
    }

    public function intervention(): BelongsTo
    {
        return $this->belongsTo(EclipIntervention::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
