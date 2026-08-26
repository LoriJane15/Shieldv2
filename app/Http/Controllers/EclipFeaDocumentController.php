<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEclipFeaDocumentRequest;
use App\Models\EclipCase;
use App\Models\EclipFeaDocument;
use App\Services\EclipDocumentStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EclipFeaDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $cases = EclipCase::query()
            ->whereHas('participantAssignments', fn ($query) => $query->where('user_id', $request->user()->id)->where('is_active', true))
            ->whereHas('workflowActivities', fn ($query) => $query->where('step_code', '4B')->whereIn('status', ['pending', 'ongoing', 'late', 'returned_for_correction']))
            ->with(['formerRebel', 'workflowActivities' => fn ($query) => $query->where('step_code', '4B')])
            ->latest()
            ->paginate(15);

        return view('eclip_fea.index', ['cases' => $cases]);
    }

    public function show(Request $request, EclipCase $eclipCase): View
    {
        $this->authorize('manageFea', $eclipCase);
        $eclipCase->load(['formerRebel.municipality', 'feaDocuments.uploader', 'workflowActivities' => fn ($query) => $query->where('step_code', '4B')]);

        return view('eclip_fea.show', ['case' => $eclipCase]);
    }

    public function store(StoreEclipFeaDocumentRequest $request, EclipCase $eclipCase, EclipDocumentStorageService $storage): RedirectResponse
    {
        $file = $request->file('document');
        $path = $storage->storeFea($file, $eclipCase->id);

        try {
            DB::transaction(function () use ($request, $eclipCase, $file, $path) {
                $eclipCase->feaDocuments()->create([
                    'document_type' => $request->validated('document_type'),
                    'storage_path' => $path,
                    'original_name' => basename(str_replace('\\', '/', $file->getClientOriginalName())),
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'size_bytes' => $file->getSize(),
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                    'uploaded_by' => $request->user()->id,
                ]);
            });
        } catch (\Throwable $exception) {
            $storage->delete($path);
            throw $exception;
        }

        return back()->with('success', 'FEA document uploaded securely.');
    }

    public function download(Request $request, EclipFeaDocument $document, EclipDocumentStorageService $storage): StreamedResponse
    {
        $document->loadMissing('eclipCase');
        $this->authorize('viewFeaDocuments', $document->eclipCase);

        return $storage->preview($document->storage_path, $document->original_name, $document->mime_type);
    }
}
