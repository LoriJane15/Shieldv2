<?php

namespace App\Http\Controllers;

use App\Models\EclipCase;
use App\Services\EclipWorkflowPresentationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EclipWorkflowController extends Controller
{
    public function show(Request $request, EclipCase $eclipCase, EclipWorkflowPresentationService $presentation): View
    {
        $this->authorize('viewWorkflow', $eclipCase);
        $eclipCase->load([
            'formerRebel.municipality',
            'participantAssignments.user',
            'workflowActivities.documents.uploader',
            'workflowActivities.histories.user',
            'statusHistories.user',
        ]);

        return view('eclip.workflow.show', [
            'case' => $eclipCase,
            'workflow' => $presentation->forCase($eclipCase, $request->user()),
            'backRoute' => $this->backRoute($request->user()->role),
        ]);
    }

    private function backRoute(string $role): string
    {
        return match ($role) {
            'mblrc' => 'mblrc.eclip.index',
            'lswdo' => 'lswdo.eclip.index',
            'eclip_assessor' => 'eclip_assessor.cases.index',
            'japic' => 'japic.eclip.index',
            'dilg_provincial_focal', 'dilg_regional', 'nboo_eclip_pmo', 'dilg_reviewer' => 'dilg_reviewer.cases.index',
            'dilg_fms', 'eclip_funding_officer' => 'eclip_funding.cases.index',
            'local_eclip_committee' => 'local_eclip.cases.index',
            'pnp' => 'pnp.eclip-fea.index',
            'afp' => 'afp.eclip-fea.index',
            'gov_agency' => 'gov_agency.eclip.basic-services.index',
            default => 'landing',
        };
    }
}
