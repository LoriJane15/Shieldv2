<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MblrcEnrollment extends Model
{
    public const STATUSES = ['in_progress', 'completed'];

    protected $fillable = [
        'former_rebel_id', 'assigned_user_id', 'created_by', 'status',
        'integration_started_at', 'integration_completed_at', 'verified_municipality_id',
        'location_verification_remarks', 'phase_one_evidence',
    ];

    protected function casts(): array
    {
        return [
            'integration_started_at' => 'date',
            'integration_completed_at' => 'date',
            'phase_one_evidence' => 'array',
        ];
    }

    public function formerRebel(): BelongsTo
    {
        return $this->belongsTo(FormerRebel::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifiedMunicipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'verified_municipality_id');
    }

    public function referral(): HasOne
    {
        return $this->hasOne(LswdoReferral::class);
    }

    public function expectedCompletionDate(): ?CarbonInterface
    {
        if (! $this->integration_started_at) {
            return null;
        }

        return CarbonImmutable::createFromFormat(
            'Y-m-d',
            $this->integration_started_at->toDateString(),
            config('app.display_timezone'),
        )->startOfDay()->addMonthsNoOverflow(3);
    }

    public function monitoringMonth(?CarbonInterface $asOf = null): ?int
    {
        if (! $this->integration_started_at) {
            return null;
        }

        if ($this->status === 'completed') {
            return 3;
        }

        $date = ($asOf ?? CarbonImmutable::now(config('app.display_timezone')))->startOfDay();

        $startedAt = CarbonImmutable::createFromFormat(
            'Y-m-d',
            $this->integration_started_at->toDateString(),
            config('app.display_timezone'),
        )->startOfDay();

        if ($date->lt($startedAt->addMonthNoOverflow())) {
            return 1;
        }

        if ($date->lt($startedAt->addMonthsNoOverflow(2))) {
            return 2;
        }

        return 3;
    }

    public function needsAttention(?CarbonInterface $asOf = null): bool
    {
        $date = ($asOf ?? CarbonImmutable::now(config('app.display_timezone')))->startOfDay();

        return $this->status === 'in_progress'
            && $this->expectedCompletionDate()?->lte($date) === true;
    }

    public function scopeNeedsAttention(Builder $query, ?CarbonInterface $asOf = null): Builder
    {
        $date = ($asOf ?? CarbonImmutable::now(config('app.display_timezone')))->toDateString();
        $driver = $query->getConnection()->getDriverName();

        return $query->where('status', 'in_progress')->whereNotNull('integration_started_at')->whereRaw(
            match ($driver) {
                'mysql', 'mariadb' => 'DATE_ADD(integration_started_at, INTERVAL 3 MONTH) <= ?',
                'pgsql' => "integration_started_at + INTERVAL '3 months' <= ?",
                default => "date(integration_started_at, '+3 months') <= ?",
            },
            [$date],
        );
    }
}
