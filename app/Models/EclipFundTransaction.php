<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EclipFundTransaction extends Model
{
    protected $fillable = [
        'eclip_case_id', 'assistance_request_id', 'assistance_revision_id', 'type',
        'amount', 'reference_number', 'transaction_date', 'remarks', 'proof_path',
        'proof_original_name', 'proof_mime_type', 'proof_size_bytes', 'proof_sha256', 'created_by',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'transaction_date' => 'date'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function eclipCase(): BelongsTo
    {
        return $this->belongsTo(EclipCase::class);
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(EclipAssistanceRevision::class, 'assistance_revision_id');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Funding transactions are immutable.'));
        static::deleting(fn () => throw new \LogicException('Funding transactions are immutable.'));
    }
}
