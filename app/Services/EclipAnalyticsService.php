<?php

namespace App\Services;

use App\Enums\EclipCaseStatus;
use App\Models\EclipAssistanceRelease;
use App\Models\EclipBasicService;
use App\Models\EclipCase;
use App\Models\EclipDocument;
use App\Models\EclipDocumentRequirement;
use App\Models\EclipFundTransaction;
use App\Models\EclipStatusHistory;
use App\Models\Municipality;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class EclipAnalyticsService
{
    public function dashboard(User $user): array
    {
        $query = $this->scopedCases($user);
        $delayDays = config('shield.eclip_delay_days', 7);
        $terminal = [
            EclipCaseStatus::Completed->value, EclipCaseStatus::Rejected->value,
            EclipCaseStatus::Ineligible->value, EclipCaseStatus::Cancelled->value,
        ];
        $statusCounts = (clone $query)->select('status')->selectRaw('COUNT(*) as aggregate')
            ->groupBy('status')->pluck('aggregate', 'status')->map(fn ($count) => (int) $count);

        $data = [
            'scopeLabel' => $this->hasNationalScope($user) ? 'All municipalities' : ($user->municipality?->name ?? 'Assigned municipality'),
            'delayDays' => $delayDays,
            'summary' => [
                'total' => (clone $query)->count(),
                'completed' => (clone $query)->where('status', EclipCaseStatus::Completed->value)->count(),
                'delayed' => (clone $query)->whereNotIn('status', $terminal)->where('updated_at', '<=', now()->subDays($delayDays))->count(),
                'returned' => EclipStatusHistory::query()->whereHas('eclipCase', fn (Builder $historyQuery) => $this->applyScope($historyQuery, $user))
                    ->whereIn('to_status', [EclipCaseStatus::ReturnedForCorrection->value, EclipCaseStatus::DocumentsIncomplete->value, EclipCaseStatus::ReturnedForAssessmentRevision->value])
                    ->distinct('eclip_case_id')->count('eclip_case_id'),
            ],
            'statusCounts' => collect(EclipCaseStatus::cases())->mapWithKeys(fn ($status) => [$status->label() => $statusCounts[$status->value] ?? 0])->all(),
            'stageHours' => $this->averageStageHours($user),
            'documentCompleteness' => $this->documentCompleteness($user),
            'basicServices' => $this->basicServiceMonitoring($user),
            'completedTrend' => $this->completedTrend($user),
            'municipalities' => $this->hasNationalScope($user) ? $this->municipalityPerformance($delayDays) : [],
            'financial' => null,
        ];

        if ($user->canViewEclipFinancialAnalytics()) {
            $data['financial'] = $this->financialTotals($user);
        }

        return $data;
    }

    public function scopedCases(User $user): Builder
    {
        return $this->applyScope(EclipCase::query(), $user);
    }

    private function applyScope(Builder $query, User $user): Builder
    {
        return $this->hasNationalScope($user)
            ? $query
            : $query->where('municipality_id', $user->municipality_id);
    }

    private function hasNationalScope(User $user): bool
    {
        return $user->hasRole('admin', 'super_admin', 'dilg_regional', 'nboo_eclip_pmo', 'dilg_fms');
    }

    private function averageStageHours(User $user): array
    {
        $histories = EclipStatusHistory::query()
            ->whereHas('eclipCase', fn (Builder $query) => $this->applyScope($query, $user))
            ->orderBy('eclip_case_id')->orderBy('created_at')->get(['eclip_case_id', 'to_status', 'created_at']);
        $durations = [];
        foreach ($histories->groupBy('eclip_case_id') as $caseHistories) {
            $rows = $caseHistories->values();
            for ($index = 0; $index < $rows->count() - 1; $index++) {
                $durations[$rows[$index]->to_status->value][] = $rows[$index]->created_at->diffInMinutes($rows[$index + 1]->created_at) / 60;
            }
        }

        return collect($durations)->mapWithKeys(function (array $hours, string $status) {
            $enum = EclipCaseStatus::tryFrom($status);

            return [$enum?->label() ?? $status => round(array_sum($hours) / count($hours), 1)];
        })->all();
    }

    private function documentCompleteness(User $user): array
    {
        $requiredCount = EclipDocumentRequirement::query()->where('is_active', true)->where('is_required', true)->count();
        $caseIds = $this->scopedCases($user)->whereNotIn('status', [
            EclipCaseStatus::Draft->value, EclipCaseStatus::SubmittedForEligibility->value,
            EclipCaseStatus::EligibilityReviewInProgress->value, EclipCaseStatus::ReturnedForCorrection->value,
            EclipCaseStatus::Eligible->value, EclipCaseStatus::Ineligible->value,
        ])->pluck('id');
        $expected = $caseIds->count() * $requiredCount;
        $certified = $expected === 0 ? 0 : EclipDocument::query()->whereIn('eclip_case_id', $caseIds)
            ->where('status', 'certified')->whereHas('requirement', fn (Builder $query) => $query->where('is_active', true)->where('is_required', true))->count();

        return ['certified' => $certified, 'expected' => $expected, 'percentage' => $expected ? round(($certified / $expected) * 100, 1) : 0];
    }

    private function financialTotals(User $user): array
    {
        $cases = $this->scopedCases($user)->with('assistanceRequest.latestRevision')->get();
        $requested = $cases->sum(fn ($case) => (float) ($case->assistanceRequest?->latestRevision?->requested_amount ?? 0));
        $assessed = $cases->sum(fn ($case) => (float) ($case->assistanceRequest?->latestRevision?->assessed_amount ?? 0));
        $scope = fn (Builder $query) => $this->applyScope($query, $user);

        return [
            'requested' => $requested,
            'assessed' => $assessed,
            'allocated' => (float) EclipFundTransaction::query()->where('type', 'allocation')->whereHas('eclipCase', $scope)->sum('amount'),
            'transferred' => (float) EclipFundTransaction::query()->where('type', 'transfer')->whereHas('eclipCase', $scope)->sum('amount'),
            'released' => (float) EclipAssistanceRelease::query()->whereHas('eclipCase', $scope)->sum('amount'),
        ];
    }

    private function basicServiceMonitoring(User $user): array
    {
        $scope = fn (Builder $query) => $this->applyScope($query, $user);
        $services = EclipBasicService::query()->whereHas('eclipCase', $scope);

        return [
            'pending' => (clone $services)->whereIn('status', ['pending', 'referred'])->count(),
            'in_progress' => (clone $services)->where('status', 'in_progress')->count(),
            'completed' => (clone $services)->where('status', 'completed')->count(),
            'overdue' => (clone $services)->whereNotIn('status', ['completed', 'not_applicable'])->whereDate('target_completion_date', '<', today())->count(),
        ];
    }

    private function completedTrend(User $user): array
    {
        $months = collect(range(11, 0))->map(fn ($offset) => now()->startOfMonth()->subMonths($offset));
        $completed = EclipStatusHistory::query()->where('to_status', EclipCaseStatus::Completed->value)
            ->whereHas('eclipCase', fn (Builder $query) => $this->applyScope($query, $user))
            ->where('created_at', '>=', $months->first())->get(['created_at']);

        return $months->mapWithKeys(fn (Carbon $month) => [
            $month->format('M Y') => $completed->filter(fn ($row) => $row->created_at->isSameMonth($month))->count(),
        ])->all();
    }

    private function municipalityPerformance(int $delayDays): array
    {
        return Municipality::query()->whereHas('eclipCases')->orderBy('name')->get()->map(function (Municipality $municipality) use ($delayDays) {
            $cases = EclipCase::query()->where('municipality_id', $municipality->id);

            return [
                'name' => $municipality->name,
                'total' => (clone $cases)->count(),
                'completed' => (clone $cases)->where('status', EclipCaseStatus::Completed->value)->count(),
                'delayed' => (clone $cases)->whereNotIn('status', [EclipCaseStatus::Completed->value, EclipCaseStatus::Rejected->value, EclipCaseStatus::Ineligible->value, EclipCaseStatus::Cancelled->value])->where('updated_at', '<=', now()->subDays($delayDays))->count(),
            ];
        })->all();
    }
}
