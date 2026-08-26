<?php

namespace App\Http\Controllers\LocalEclip;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocalEclip\IndexSurfacedFormerRebelsRequest;
use App\Services\LocalEclipSurfacedWorkspaceService;
use Illuminate\View\View;

class SurfacedFormerRebelController extends Controller
{
    public function index(IndexSurfacedFormerRebelsRequest $request, LocalEclipSurfacedWorkspaceService $workspace): View
    {
        return view('local_eclip.surfaced.index', [
            'records' => $workspace->forUser($request->user(), $request->validated()),
            'statusOptions' => collect(EclipCaseStatus::cases())
                ->reject(fn (EclipCaseStatus $status) => $status === EclipCaseStatus::Draft)
                ->mapWithKeys(fn (EclipCaseStatus $status) => [$status->value => $status->label()]),
        ]);
    }
}
