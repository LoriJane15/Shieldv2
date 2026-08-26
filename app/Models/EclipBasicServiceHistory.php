<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipBasicServiceHistory extends Model
{
    protected $fillable = ['basic_service_id', 'user_id', 'action', 'previous_values', 'new_values', 'ip_address'];

    protected function casts(): array
    {
        return ['previous_values' => 'array', 'new_values' => 'array'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(EclipBasicService::class, 'basic_service_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
