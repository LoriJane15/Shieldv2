<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipLiquidationRequirement extends Model
{
    public const STATUSES = ['complete', 'missing', 'uploaded_in_eclip_is', 'returned', 'resubmitted', 'accepted'];

    protected $fillable = ['assistance_category', 'requirement_name', 'status', 'reference', 'submitted_at', 'returned_at', 'return_reason', 'resubmitted_at', 'accepted_at', 'remarks', 'updated_by'];

    protected function casts(): array
    {
        return ['submitted_at' => 'date', 'returned_at' => 'date', 'resubmitted_at' => 'date', 'accepted_at' => 'date'];
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
