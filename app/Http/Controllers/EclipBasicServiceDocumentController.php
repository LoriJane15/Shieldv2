<?php

namespace App\Http\Controllers;

use App\Models\EclipBasicService;
use App\Models\EclipBasicServiceDocument;
use App\Services\EclipBasicServiceDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EclipBasicServiceDocumentController extends Controller
{
    public function store(Request $request, EclipBasicService $basicService, EclipBasicServiceDocumentService $documents): RedirectResponse
    {
        $this->authorize('uploadDocument', $basicService);
        $validated = $request->validate(['document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240']]);
        $documents->store($basicService, $validated['document'], $request->user(), $request->ip());

        return back()->with('success', 'Supporting document uploaded.');
    }

    public function download(EclipBasicServiceDocument $document, EclipBasicServiceDocumentService $documents): StreamedResponse
    {
        $this->authorize('downloadDocument', $document->service);

        return $documents->download($document);
    }
}
