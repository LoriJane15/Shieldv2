<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrGovernmentAssistance extends Model
{
    protected $fillable = [
        'former_rebel_id', 'assistance_type', 'amount_or_value', 'provider', 'date_received',
        'status', 'remarks', 'certificate_file', 'source_type', 'source_id',
    ];

    protected function casts(): array
    {
        return ['amount_or_value' => 'decimal:2', 'date_received' => 'date'];
    }

    public function formerRebel(): BelongsTo
    {
        return $this->belongsTo(FormerRebel::class);
    }
}
