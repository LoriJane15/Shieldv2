<?php

namespace App\Http\Controllers\GovAgency;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEclipBasicServiceRequest;
use App\Models\EclipBasicService;
use App\Services\EclipBasicServiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EclipBasicServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = EclipBasicService::query()->where('gov_agency_id', $request->user()->gov_agency_id)
            ->with(['eclipCase.formerRebel', 'eclipCase.municipality', 'documents.uploader'])
            ->latest('updated_at')->paginate(20);

        return view('gov_agency.eclip-basic-services.index', ['services' => $services, 'types' => config('shield.eclip_basic_service_types')]);
    }

    public function update(UpdateEclipBasicServiceRequest $request, EclipBasicService $basicService, EclipBasicServiceService $services): RedirectResponse
    {
        $services->update($basicService, $request->validated(), $request->user(), $request->ip());

        return back()->with('success', 'Service delivery status updated.');
    }
}
