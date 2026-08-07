<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipWorkflowActivityHistory extends Model
{
    protected $fillable = ['user_id', 'from_status', 'to_status', 'remarks', 'data', 'ip_address'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(EclipWorkflowActivity::class, 'activity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
