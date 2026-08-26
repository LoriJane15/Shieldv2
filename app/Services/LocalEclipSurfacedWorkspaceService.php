<?php

namespace App\Services;

use App\Models\FormerRebel;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LocalEclipSurfacedWorkspaceService
{
    public function forUser(User $user, array $filters): LengthAwarePaginator
    {
        $municipalityId = $user->municipality_id;
        $search = $filters['search'] ?? null;
        $caseStatus = $filters['case_status'] ?? null;

        $beneficiaries = FormerRebel::query()
            ->select(['id', 'classified_id', 'municipality_id', 'surrender_date', 'placement_address', 'registered_at'])
            ->where(fn ($query) => $query
                ->where('municipality_id', $municipalityId)
                ->orWhereHas('eclipCases', fn ($cases) => $cases->where('municipality_id', $municipalityId)))
            ->when($search, function ($query, string $value) use ($municipalityId) {
                $escaped = addcslashes($value, '%_\\');
                $query->where(function ($searchQuery) use ($escaped, $municipalityId) {
                    $searchQuery->where('classified_id', 'like', "%{$escaped}%")
                        ->orWhereHas('eclipCases', fn ($cases) => $cases
                            ->where('municipality_id', $municipalityId)
                            ->where('case_number', 'like', "%{$escaped}%"));
                });
            })
            ->when($caseStatus, fn ($query, string $status) => $query->whereHas('eclipCases', fn ($cases) => $cases
                ->where('municipality_id', $municipalityId)
                ->where('status', $status)))
            ->with([
                'municipality:id,name',
                'eclipCases' => fn ($query) => $query
                    ->where('municipality_id', $municipalityId)
                    ->with('workflowActivities:id,eclip_case_id,step_code,status,data')
                    ->latest('updated_at'),
            ])
            ->latest('registered_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $beneficiaries->setCollection($beneficiaries->getCollection()->map(function (FormerRebel $beneficiary) use ($user) {
            $case = $beneficiary->eclipCases->first();
            $surfacing = $case?->workflowActivities->firstWhere('step_code', '1');
            $activities = $case?->workflowActivities ?? collect();
            $completedSteps = $activities->whereIn('status', ['completed', 'not_applicable'])->count();

            return [
                'beneficiary' => $beneficiary,
                'case' => $case,
                'surfaced_at' => data_get($surfacing?->data, 'intention_date') ?: $beneficiary->surrender_date?->toDateString(),
                'surfaced_location' => data_get($surfacing?->data, 'receiving_unit')
                    ?: $beneficiary->placement_address
                    ?: $beneficiary->municipality?->name,
                'completed_steps' => $completedSteps,
                'total_steps' => $activities->count(),
                'can_open_workflow' => $case ? $user->can('viewWorkflow', $case) : false,
            ];
        }));

        return $beneficiaries;
    }
}
