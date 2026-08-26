<?php

namespace App\Http\Controllers\DilgReviewer;

use App\Http\Controllers\Controller;
use App\Http\Requests\DilgReviewer\SaveLiquidationRequirementRequest;
use App\Http\Requests\DilgReviewer\SaveRegionalDisbursementReportRequest;
use App\Models\EclipCase;
use App\Models\EclipLiquidationRequirement;
use App\Models\EclipRegionalDisbursementReport;
use App\Services\EclipFinancialMonitoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EclipFinancialMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasRole('dilg_provincial_focal', 'dilg_regional'), 403);
        $responsibleRole = $request->user()->hasRole('dilg_provincial_focal') ? 'dilg_provincial_focal' : 'dilg_regional';
        $cases = EclipCase::query()
            ->when($request->user()->hasRole('dilg_provincial_focal'), fn ($query) => $query->where('municipality_id', $request->user()->municipality_id))
            ->whereHas('workflowActivities', fn ($query) => $query
                ->where('status', '!=', 'locked')
                ->whereJsonContains('responsible_roles', $responsibleRole))
            ->with('formerRebel.municipality')
            ->latest()
            ->paginate(15);

        return view('dilg_reviewer.financial-monitoring.index', compact('cases'));
    }

    public function show(Request $request, EclipCase $eclipCase): View
    {
        $this->authorize('viewWorkflow', $eclipCase);
        $eclipCase->load([
            'formerRebel.municipality',
            'liquidationRequirements.updater',
            'regionalDisbursementReports.updater',
        ]);

        return view('dilg_reviewer.financial-monitoring.show', ['case' => $eclipCase]);
    }

    public function storeLiquidation(SaveLiquidationRequirementRequest $request, EclipCase $eclipCase, EclipFinancialMonitoringService $service): RedirectResponse
    {
        $service->createLiquidation($eclipCase, $request->validated(), $request->user(), $request->ip(), $request->userAgent());

        return back()->with('success', 'Liquidation requirement recorded.');
    }

    public function updateLiquidation(SaveLiquidationRequirementRequest $request, EclipLiquidationRequirement $liquidationRequirement, EclipFinancialMonitoringService $service): RedirectResponse
    {
        $service->updateLiquidation($liquidationRequirement, $request->validated(), $request->user(), $request->ip(), $request->userAgent());

        return back()->with('success', 'Liquidation requirement updated with an audit snapshot.');
    }

    public function storeReport(SaveRegionalDisbursementReportRequest $request, EclipCase $eclipCase, EclipFinancialMonitoringService $service): RedirectResponse
    {
        $service->createReport($eclipCase, $request->validated(), $request->user(), $request->ip(), $request->userAgent());

        return back()->with('success', 'Regional disbursement report recorded.');
    }

    public function updateReport(SaveRegionalDisbursementReportRequest $request, EclipRegionalDisbursementReport $disbursementReport, EclipFinancialMonitoringService $service): RedirectResponse
    {
        $service->updateReport($disbursementReport, $request->validated(), $request->user(), $request->ip(), $request->userAgent());

        return back()->with('success', 'Regional disbursement report updated with an audit snapshot.');
    }
}
