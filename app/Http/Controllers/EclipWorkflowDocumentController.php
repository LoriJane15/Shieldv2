<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEclipWorkflowDocumentRequest;
use App\Models\EclipWorkflowActivity;
use App\Models\EclipWorkflowDocument;
use App\Services\EclipDocumentStorageService;
use App\Services\EclipWorkflowDocumentService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EclipWorkflowDocumentController extends Controller
{
    public function store(StoreEclipWorkflowDocumentRequest $request, EclipWorkflowActivity $activity, EclipWorkflowDocumentService $documents): RedirectResponse
    {
        $documents->store(
            $activity,
            $request->validated('document_type'),
            $request->file('document'),
            $request->validated('remarks'),
            $request->user(),
            $request->ip(),
        );

        return back()->with('success', "Evidence uploaded for Step {$activity->step_code}.");
    }

    public function download(EclipWorkflowDocument $document, EclipDocumentStorageService $storage): StreamedResponse
    {
        $document->loadMissing('activity.eclipCase');
        $this->authorize('viewWorkflow', $document->activity->eclipCase);

        return $storage->download($document->storage_path, $document->original_name, $document->mime_type);
    }
}
