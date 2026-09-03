<?php

namespace App\Http\Controllers\Ib39;

use App\Enums\Ib39FrCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ib39\IndexSurfacedFormerRebelRequest;
use App\Http\Requests\Ib39\StoreSurfacedFormerRebelRequest;
use App\Models\Ib39SurfacedFormerRebel;
use App\Models\Municipality;
use App\Services\Ib39SurfacedFormerRebelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SurfacedFormerRebelController extends Controller
{
    public function index(IndexSurfacedFormerRebelRequest $request): View
    {
        Gate::authorize('viewAny', Ib39SurfacedFormerRebel::class);

        $filters = $request->validatedFilters();
        $search = $filters['search'] ?? null;

        $records = Ib39SurfacedFormerRebel::query()
            ->with(['municipality', 'barangay', 'cdrProcessing:id,ib39_surfaced_former_rebel_id,status'])
            ->when($search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->where('category', $category))
            ->when($filters['municipality_id'] ?? null, fn ($query, int $municipalityId) => $query->where('municipality_id', $municipalityId))
            ->when(array_key_exists('possessed_firearms', $filters), fn ($query) => $query->where('possessed_firearms', $filters['possessed_firearms']))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($filters);

        return view('ib39.fr-profiles.index', [
            'records' => $records,
            'categories' => Ib39FrCategory::cases(),
            'municipalities' => Municipality::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'hasActiveFilters' => collect($filters)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Ib39SurfacedFormerRebel::class);

        return view('ib39.fr-profiles.create', [
            'categories' => Ib39FrCategory::cases(),
            'municipalities' => Municipality::query()
                ->with(['barangays' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(),
            'province' => Ib39SurfacedFormerRebel::DEFAULT_PROVINCE,
        ]);
    }

    public function show(Ib39SurfacedFormerRebel $ib39SurfacedFormerRebel): View
    {
        Gate::authorize('view', $ib39SurfacedFormerRebel);

        $ib39SurfacedFormerRebel->load(['municipality', 'barangay', 'creator', 'cdrProcessing', 'feaProcessing.documents']);

        return view('ib39.fr-profiles.show', [
            'record' => $ib39SurfacedFormerRebel,
            'recordedBy' => filled($ib39SurfacedFormerRebel->creator?->name)
                ? $ib39SurfacedFormerRebel->creator->name
                : 'Unknown user',
        ]);
    }

    public function store(
        StoreSurfacedFormerRebelRequest $request,
        Ib39SurfacedFormerRebelService $records,
    ): RedirectResponse {
        Gate::authorize('create', Ib39SurfacedFormerRebel::class);

        $record = $records->create(
            $request->validatedForCreation(),
            $request->user(),
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route('ib39.dashboard')
            ->with('success', "Surfaced FR {$record->reference_number} was recorded successfully.");
    }
}
