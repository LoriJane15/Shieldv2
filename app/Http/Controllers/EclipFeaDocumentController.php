<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEclipFeaDocumentRequest;
use App\Models\EclipCase;
use App\Models\EclipFeaDocument;
use App\Services\EclipDocumentStorageService;
use App\Services\EclipFeaDocumentService;
use App\Services\EclipFeaWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EclipFeaDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $cases = EclipCase::query()
            ->whereHas('participantAssignments', fn ($query) => $query
                ->where('user_id', $request->user()->id)
                ->where('participant_role', 'fea_processor')
                ->where('is_active', true))
            ->whereHas('workflowActivities', fn ($query) => $query->where('step_code', '4B')->whereIn('status', ['pending', 'ongoing', 'late', 'returned_for_correction']))
            ->with(['formerRebel', 'workflowActivities' => fn ($query) => $query->where('step_code', '4B')])
            ->latest()
            ->paginate(15);

        return view('eclip_fea.index', ['cases' => $cases]);
    }

    public function show(Request $request, EclipCase $eclipCase, EclipFeaWorkspaceService $workspace): View
    {
        $this->authorize('manageFea', $eclipCase);

        return view('eclip_fea.show', [
            'case' => $eclipCase,
            'workspace' => $workspace->forCase($eclipCase, $request->user()),
        ]);
    }

    public function store(StoreEclipFeaDocumentRequest $request, EclipCase $eclipCase, EclipFeaDocumentService $documents): RedirectResponse
    {
        $documents->store(
            $eclipCase,
            $request->validated('document_type'),
            $request->file('document'),
            $request->user(),
            $request->ip(),
        );

        return back()->with('success', 'FEA document uploaded securely.');
    }

    public function download(Request $request, EclipFeaDocument $document, EclipDocumentStorageService $storage): StreamedResponse
    {
        $document->loadMissing('eclipCase');
        $this->authorize('viewFeaDocuments', $document->eclipCase);

        return $storage->preview($document->storage_path, $document->original_name, $document->mime_type);
    }
}
