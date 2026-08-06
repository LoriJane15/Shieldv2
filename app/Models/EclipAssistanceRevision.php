<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipAssistanceRevision extends Model
{
    protected $fillable = [
        'assistance_request_id', 'category_id', 'revision_number', 'requested_amount',
        'assessed_amount', 'justification', 'assessment_remarks', 'created_by',
    ];

    protected function casts(): array
    {
        return ['requested_amount' => 'decimal:2', 'assessed_amount' => 'decimal:2'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EclipAssistanceCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
