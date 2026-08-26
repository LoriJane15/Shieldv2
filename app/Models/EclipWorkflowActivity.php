<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EclipWorkflowActivity extends Model
{
    protected $fillable = ['step_code', 'phase', 'title', 'status', 'responsible_roles', 'required_documents', 'available_at', 'started_at', 'due_at', 'completed_at', 'completed_by', 'remarks', 'data'];

    protected function casts(): array
    {
        return [
            'responsible_roles' => 'array', 'required_documents' => 'array', 'data' => 'array',
            'available_at' => 'datetime', 'started_at' => 'datetime', 'due_at' => 'datetime', 'completed_at' => 'datetime',
        ];
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EclipWorkflowActivityHistory::class, 'activity_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EclipWorkflowDocument::class, 'activity_id');
    }
}
