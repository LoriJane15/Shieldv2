<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEclipWorkflowActivityRequest;
use App\Models\EclipWorkflowActivity;
use App\Services\EclipOfficialWorkflowService;
use Illuminate\Http\RedirectResponse;

class EclipWorkflowActivityController extends Controller
{
    public function update(UpdateEclipWorkflowActivityRequest $request, EclipWorkflowActivity $activity, EclipOfficialWorkflowService $workflow): RedirectResponse
    {
        $workflow->update($activity, $request->user(), $request->string('status')->toString(), $request->input('remarks'), $request->input('data', []), $request->ip());

        return back()->with('success', "Step {$activity->step_code} was updated.");
    }
}
