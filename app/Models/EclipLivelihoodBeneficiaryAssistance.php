<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EclipLivelihoodBeneficiaryAssistance extends Model
{
    public const APPROVAL_STATUSES = ['pending', 'approved', 'rejected'];

    public const RELEASE_STATUSES = ['pending', 'scheduled', 'released', 'returned', 'not_applicable'];

    protected $fillable = [
        'implementation_reason', 'beneficiary_name', 'relationship', 'approval_reference',
        'approval_status', 'assistance_amount', 'release_status', 'release_date',
        'supporting_reference', 'remarks', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'beneficiary_name' => 'encrypted',
            'assistance_amount' => 'decimal:2',
            'release_date' => 'date',
        ];
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EclipLivelihoodBeneficiaryAssistanceHistory::class, 'assistance_id');
    }
}
