<?php

namespace App\Http\Controllers\GovAgency;

use App\Http\Controllers\Controller;
use App\Models\AgencyImplanResponse;
use App\Models\GovAgency;
use App\Models\Implementation;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $agencyId = auth()->user()->gov_agency_id;

        $assigned = Implementation::whereJsonContains('agencies', $agencyId);

        $acceptedIds = AgencyImplanResponse::where('gov_agency_id', $agencyId)
            ->where('response_status', 'accepted')->pluck('implementation_id');

        $stats = [
            'assigned' => (clone $assigned)->count(),
            'accepted' => $acceptedIds->count(),
            'pending' => AgencyImplanResponse::where('gov_agency_id', $agencyId)
                ->where('response_status', 'pending')->count(),
            'rejected' => AgencyImplanResponse::where('gov_agency_id', $agencyId)
                ->where('response_status', 'rejected')->count(),
        ];

        $accepted = Implementation::whereIn('id', $acceptedIds)
            ->latest()->take(10)->get();

        return view('gov_agency.dashboard', [
            'stats' => $stats,
            'accepted' => $accepted,
            'agency' => auth()->user()->govAgency,
            'agenciesById' => GovAgency::all()->keyBy('id'),
        ]);
    }
}
