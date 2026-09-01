<?php

namespace App\Http\Controllers\Ib39;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ib39\SaveFeaDraftRequest;
use App\Models\Ib39FeaDocument;
use App\Models\Ib39FeaProcessing;
use App\Services\Ib39FeaDocumentWorkflowService;
use App\Services\Ib39FeaDraftSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FeaDraftController extends Controller
{
    public function edit(Ib39FeaProcessing $fea, Ib39FeaDocument $document, Ib39FeaDraftSchema $schema): View
    {
        Gate::authorize('editDraft', [$document, $fea]);
        $fea->load('surfacedFormerRebel');
        $document->load(['draftSaver:id,name', 'draftHistories' => fn ($query) => $query->with('actor:id,name')->latest('revision')]);
        $fields = $schema->fields($document->document_type);
        $draft = $document->draft_data ?? $schema->initial($document->document_type, $fea->surfacedFormerRebel->display_name);

        return view('ib39.fea.draft', compact('fea', 'document', 'fields', 'draft'));
    }

    public function update(
        SaveFeaDraftRequest $request,
        Ib39FeaProcessing $fea,
        Ib39FeaDocument $document,
        Ib39FeaDocumentWorkflowService $workflow,
    ): RedirectResponse {
        $saved = $workflow->saveDraft($fea, $document, $request->draftData(), (int) $request->validated('revision'), $request->user());

        return redirect()->route('ib39.fea.documents.draft.edit', [$fea, $document])
            ->with('status', 'Draft revision '.$saved->draft_revision.' saved securely.');
    }
}
