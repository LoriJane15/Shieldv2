<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipRegionalDisbursementReport extends Model
{
    public const STATUSES = ['preparing', 'submitted', 'copy_furnished', 'returned', 'resubmitted', 'accepted'];

    protected $fillable = ['reporting_month', 'form_11_reference', 'status', 'submitted_at', 'returned_at', 'return_reason', 'resubmitted_at', 'accepted_at', 'remarks', 'updated_by'];

    protected function casts(): array
    {
        return ['reporting_month' => 'date', 'submitted_at' => 'date', 'returned_at' => 'date', 'resubmitted_at' => 'date', 'accepted_at' => 'date'];
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
