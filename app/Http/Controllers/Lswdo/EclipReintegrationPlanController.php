<?php

namespace App\Http\Controllers\Lswdo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lswdo\StoreEclipReintegrationPlanItemRequest;
use App\Http\Requests\Lswdo\UpdateEclipReintegrationPlanItemRequest;
use App\Models\EclipCase;
use App\Models\EclipReintegrationPlanItem;
use App\Services\EclipReintegrationPlanService;
use Illuminate\Http\RedirectResponse;

class EclipReintegrationPlanController extends Controller
{
    public function store(StoreEclipReintegrationPlanItemRequest $request, EclipCase $eclipCase, EclipReintegrationPlanService $service): RedirectResponse
    {
        $service->create($eclipCase, $request->validated(), $request->user(), $request->ip());

        return back()->with('success', 'Reintegration plan item recorded.');
    }

    public function update(UpdateEclipReintegrationPlanItemRequest $request, EclipReintegrationPlanItem $planItem, EclipReintegrationPlanService $service): RedirectResponse
    {
        $service->update($planItem, $request->validated(), $request->user(), $request->ip());

        return back()->with('success', 'Reintegration plan item updated.');
    }
}
