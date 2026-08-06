<?php

namespace App\Http\Controllers\Mblrc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mblrc\UploadEclipDocumentRequest;
use App\Models\EclipCase;
use App\Models\EclipDocumentRequirement;
use App\Services\EclipDocumentService;
use Illuminate\Http\RedirectResponse;

class EclipDocumentController extends Controller
{
    public function store(
        UploadEclipDocumentRequest $request,
        EclipCase $eclipCase,
        EclipDocumentService $documents,
    ): RedirectResponse {
        $requirement = EclipDocumentRequirement::query()->findOrFail($request->integer('requirement_id'));
        $documents->upload($eclipCase, $requirement, $request->file('document'), $request->user(), $request->ip());

        return back()->with('success', 'Document uploaded for JAPIC review.');
    }
}
