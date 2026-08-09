<?php

namespace App\Http\Controllers\Lswdo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lswdo\StoreEclipBasicServiceRequest;
use App\Http\Requests\UpdateEclipBasicServiceRequest;
use App\Models\EclipBasicService;
use App\Models\EclipCase;
use App\Models\GovAgency;
use App\Services\EclipBasicServiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EclipBasicServiceController extends Controller
{
    public function index(Request $request, EclipCase $eclipCase): View
    {
        abort_unless($request->user()->municipality_id === $eclipCase->municipality_id, 403);
        $eclipCase->load([
            'formerRebel',
            'basicServices' => fn ($query) => $query->latest('updated_at'),
            'basicServices.agency',
            'basicServices.updater',
            'basicServices.documents' => fn ($query) => $query->latest('version_number'),
            'basicServices.documents.uploader',
            'basicServices.histories' => fn ($query) => $query->latest()->limit(5),
            'basicServices.histories.user',
        ]);

        return view('lswdo.eclip.basic-services', ['case' => $eclipCase, 'agencies' => GovAgency::query()->orderBy('name')->get(), 'types' => config('shield.eclip_basic_service_types')]);
    }

    public function store(StoreEclipBasicServiceRequest $request, EclipCase $eclipCase, EclipBasicServiceService $services): RedirectResponse
    {
        $services->create($eclipCase, $request->validated(), $request->user(), $request->ip());

        return back()->with('success', 'Basic-service referral recorded.');
    }

    public function update(UpdateEclipBasicServiceRequest $request, EclipBasicService $basicService, EclipBasicServiceService $services): RedirectResponse
    {
        $services->update($basicService, $request->validated(), $request->user(), $request->ip());

        return back()->with('success', 'Basic-service record updated.');
    }
}
