<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipWorkflowActivityHistory extends Model
{
    protected $fillable = [
        'user_id', 'event', 'actor_role', 'actor_office', 'from_status', 'to_status',
        'remarks', 'data', 'document_id', 'ip_address',
    ];

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

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Workflow history entries are immutable.'));
        static::deleting(fn () => throw new \LogicException('Workflow history entries are immutable.'));
    }
}
