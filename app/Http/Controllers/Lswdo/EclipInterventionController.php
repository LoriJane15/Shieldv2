<?php

namespace App\Http\Controllers\Lswdo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lswdo\StoreEclipInterventionRequest;
use App\Http\Requests\Lswdo\UpdateEclipInterventionRequest;
use App\Models\EclipCase;
use App\Models\EclipIntervention;
use App\Services\EclipInterventionService;
use Illuminate\Http\RedirectResponse;

class EclipInterventionController extends Controller
{
    public function store(StoreEclipInterventionRequest $request, EclipCase $eclipCase, EclipInterventionService $interventions): RedirectResponse
    {
        $interventions->create($eclipCase, $request->validated(), $request->user(), $request->ip());

        return back()->with('success', 'Intervention recorded.');
    }

    public function update(UpdateEclipInterventionRequest $request, EclipIntervention $eclipIntervention, EclipInterventionService $interventions): RedirectResponse
    {
        $interventions->update($eclipIntervention, $request->validated(), $request->user(), $request->ip());

        return back()->with('success', 'Intervention progress updated.');
    }
}
