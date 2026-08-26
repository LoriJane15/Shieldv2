<?php

namespace App\Http\Controllers\Lswdo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lswdo\AssignFeaProcessorRequest;
use App\Models\EclipCase;
use App\Models\User;
use App\Services\EclipFeaAssignmentService;
use Illuminate\Http\RedirectResponse;

class EclipFeaAssignmentController extends Controller
{
    public function store(AssignFeaProcessorRequest $request, EclipCase $eclipCase, EclipFeaAssignmentService $assignments): RedirectResponse
    {
        $processor = User::query()->findOrFail($request->integer('processor_id'));
        $assignments->assign($eclipCase, $processor, $request->user(), $request->ip());

        return back()->with('success', 'FEA processor assigned.');
    }
}
