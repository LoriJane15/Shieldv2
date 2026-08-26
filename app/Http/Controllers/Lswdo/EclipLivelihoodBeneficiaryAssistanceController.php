<?php

namespace App\Http\Controllers\Lswdo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lswdo\StoreEclipLivelihoodBeneficiaryAssistanceRequest;
use App\Http\Requests\Lswdo\UpdateEclipLivelihoodBeneficiaryAssistanceRequest;
use App\Models\EclipCase;
use App\Models\EclipLivelihoodBeneficiaryAssistance;
use App\Services\EclipLivelihoodBeneficiaryAssistanceService;
use Illuminate\Http\RedirectResponse;

class EclipLivelihoodBeneficiaryAssistanceController extends Controller
{
    public function store(StoreEclipLivelihoodBeneficiaryAssistanceRequest $request, EclipCase $eclipCase, EclipLivelihoodBeneficiaryAssistanceService $service): RedirectResponse
    {
        $service->create($eclipCase, $request->validated(), $request->user(), $request->ip());

        return back()->with('success', 'Identified-beneficiary livelihood assistance recorded.');
    }

    public function update(UpdateEclipLivelihoodBeneficiaryAssistanceRequest $request, EclipLivelihoodBeneficiaryAssistance $livelihoodAssistance, EclipLivelihoodBeneficiaryAssistanceService $service): RedirectResponse
    {
        $service->update($livelihoodAssistance, $request->validated(), $request->user(), $request->ip());

        return back()->with('success', 'Identified-beneficiary livelihood assistance updated.');
    }
}
