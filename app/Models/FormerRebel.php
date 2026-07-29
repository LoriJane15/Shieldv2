<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormerRebel extends Model
{
    protected $fillable = [
        'classified_id', 'firstname', 'lastname', 'middlename', 'nickname', 'suffix',
        'gender', 'age', 'civil_status', 'residential_address', 'placement_address',
        'birthdate', 'contact_num', 'batch_year', 'batch_section', 'zipcode',
        'barangay_id', 'municipality_id', 'province', 'surrender_date', 'surrender_reason',
        'registered_at', 'status', 'latitude', 'longitude', 'occupation', 'work_status',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'surrender_date' => 'date',
            'registered_at' => 'date',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->middlename} {$this->lastname} {$this->suffix}");
    }

    /** Next sequential classified id, e.g. FR-#0001 (replaces getNextFRClassifiedID). */
    public static function nextClassifiedId(): string
    {
        $last = static::query()->orderByDesc('id')->value('classified_id');
        $n = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'FR-#'.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function programStatus(): HasOne
    {
        return $this->hasOne(FrProgramStatus::class);
    }

    public function educationWorks(): HasMany
    {
        return $this->hasMany(FrEducationWork::class);
    }

    public function locationHistories(): HasMany
    {
        return $this->hasMany(FrLocationHistory::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(FrSkill::class);
    }

    public function assistances(): HasMany
    {
        return $this->hasMany(FrGovernmentAssistance::class);
    }

    public function hasRecordedHistory(): bool
    {
        return $this->programStatus()->exists()
            || $this->educationWorks()->exists()
            || $this->locationHistories()->exists()
            || $this->skills()->exists()
            || $this->assistances()->exists();
    }
}
