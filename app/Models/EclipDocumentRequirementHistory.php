<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipDocumentRequirementHistory extends Model
{
    protected $fillable = ['requirement_id', 'user_id', 'action', 'new_values'];

    protected function casts(): array
    {
        return ['new_values' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
