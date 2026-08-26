<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\IndexAuditLogsRequest;
use App\Models\AuditLog;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(IndexAuditLogsRequest $request): View
    {
        $filters = $request->validated();
        [$dateFrom, $dateTo] = $this->dateBounds($filters);

        $logs = AuditLog::query()
            ->with([
                'user:id,name,username,role,municipality_id,gov_agency_id',
                'user.municipality:id,name',
                'user.govAgency:id,name,acronym',
            ])
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $escapedSearch = addcslashes($search, '%_\\');
                $numericSearch = ctype_digit($search) ? (int) $search : null;
                $eventId = preg_match('/^AUD-0*(\d+)$/i', $search, $matches) ? (int) $matches[1] : null;

                $query->where(function (Builder $searchQuery) use ($escapedSearch, $numericSearch, $eventId) {
                    $searchQuery->where('entity_type', 'like', "%{$escapedSearch}%")
                        ->orWhere('action', 'like', "%{$escapedSearch}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$escapedSearch}%")
                            ->orWhere('username', 'like', "%{$escapedSearch}%"));

                    if ($numericSearch !== null) {
                        $searchQuery->orWhere('entity_id', $numericSearch)
                            ->orWhere('user_id', $numericSearch)
                            ->orWhere('id', $numericSearch);
                    }

                    if ($eventId !== null) {
                        $searchQuery->orWhere('id', $eventId);
                    }
                });
            })
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['module'] ?? null, fn ($query, string $module) => $query->where('entity_type', $module))
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('role', $role)))
            ->when($dateFrom, fn ($query) => $query->where('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->where('created_at', '<=', $dateTo))
            ->orderBy('created_at', ($filters['sort'] ?? 'newest') === 'oldest' ? 'asc' : 'desc')
            ->orderBy('id', ($filters['sort'] ?? 'newest') === 'oldest' ? 'asc' : 'desc')
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();

        return view('super_admin.audit-logs.index', [
            'logs' => $logs,
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'modules' => $this->moduleOptions(),
            'roles' => $this->roleOptions(),
            'filters' => $filters,
            'hasActiveFilters' => collect($filters)->except(['sort', 'per_page', 'date_range'])
                ->filter(fn ($value) => filled($value))->isNotEmpty()
                || ($filters['date_range'] ?? 'all') !== 'all'
                || ($filters['sort'] ?? 'newest') !== 'newest'
                || (int) ($filters['per_page'] ?? 25) !== 25,
        ]);
    }

    private function moduleOptions(): Collection
    {
        return AuditLog::query()
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type')
            ->mapWithKeys(fn (string $entityType) => [
                $entityType => (new AuditLog(['entity_type' => $entityType]))->moduleLabel().' · '.str(class_basename($entityType))->headline(),
            ]);
    }

    private function roleOptions(): Collection
    {
        return AuditLog::query()
            ->join('users', 'users.id', '=', 'audit_logs.user_id')
            ->whereNotNull('users.role')
            ->distinct()
            ->orderBy('users.role')
            ->pluck('users.role')
            ->mapWithKeys(fn (string $role) => [
                $role => config("shield.roles.{$role}.label", str($role)->replace('_', ' ')->title()),
            ]);
    }

    private function dateBounds(array $filters): array
    {
        $timezone = (string) config('app.display_timezone');
        $now = CarbonImmutable::now($timezone);

        return match ($filters['date_range'] ?? 'all') {
            'today' => [$now->startOfDay()->utc(), $now->endOfDay()->utc()],
            'last_7_days' => [$now->subDays(6)->startOfDay()->utc(), $now->endOfDay()->utc()],
            'last_30_days' => [$now->subDays(29)->startOfDay()->utc(), $now->endOfDay()->utc()],
            'this_month' => [$now->startOfMonth()->utc(), $now->endOfDay()->utc()],
            'custom' => [
                CarbonImmutable::createFromFormat('Y-m-d', $filters['date_from'], $timezone)->startOfDay()->utc(),
                CarbonImmutable::createFromFormat('Y-m-d', $filters['date_to'], $timezone)->endOfDay()->utc(),
            ],
            default => [null, null],
        };
    }
}
