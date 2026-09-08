<?php

namespace App\Models;

use App\Enums\Ib39CdrStatus;
use App\Enums\Ib39FrCategory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ib39SurfacedFormerRebel extends Model
{
    use SoftDeletes;

    public const DEFAULT_PROVINCE = 'Davao del Sur';

    public const OVERALL_CASE_STATUS = 'Newly Recorded';

    public const OVERALL_CASE_STATUS_CDR_ONGOING = 'CDR Ongoing';

    public const OVERALL_CASE_STATUS_CDR_COMPLETED = 'CDR Completed';

    public const OVERALL_CASE_STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'first_name',
        'last_name',
        'category',
        'other_category_specification',
        'province',
        'municipality_id',
        'barangay_id',
        'specific_location',
        'surfaced_at',
        'possessed_firearms',
        'initial_remarks',
    ];

    protected function casts(): array
    {
        return [
            'category' => Ib39FrCategory::class,
            'surfaced_at' => 'date',
            'possessed_firearms' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim(
                trim((string) $this->first_name).' '.trim((string) $this->last_name)
            ),
        );
    }

    protected function overallCaseStatus(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->relationLoaded('cancellation') && $this->cancellation
                ? self::OVERALL_CASE_STATUS_CANCELLED
                : match ($this->loadedCdrStatus()) {
                    Ib39CdrStatus::Ongoing => self::OVERALL_CASE_STATUS_CDR_ONGOING,
                    Ib39CdrStatus::Completed => self::OVERALL_CASE_STATUS_CDR_COMPLETED,
                    default => self::OVERALL_CASE_STATUS,
                },
        );
    }

    protected function cdrStatus(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->loadedCdrStatus()?->value ?? 'Not Available',
        );
    }

    private function loadedCdrStatus(): ?Ib39CdrStatus
    {
        if (! $this->relationLoaded('cdrProcessing')) {
            return null;
        }

        return $this->cdrProcessing?->status;
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cdrProcessing(): HasOne
    {
        return $this->hasOne(Ib39CdrProcessing::class, 'ib39_surfaced_former_rebel_id');
    }

    public function feaProcessing(): HasOne
    {
        return $this->hasOne(Ib39FeaProcessing::class, 'ib39_surfaced_former_rebel_id');
    }

    public function cancellation(): HasOne
    {
        return $this->hasOne(Ib39FrCancellation::class, 'ib39_surfaced_former_rebel_id');
    }
}
