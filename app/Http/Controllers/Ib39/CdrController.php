<?php

namespace App\Http\Controllers\Ib39;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ib39\SaveCdrDraftRequest;
use App\Http\Requests\Ib39\StartCdrProcessingRequest;
use App\Models\Ib39CdrProcessing;
use App\Services\Ib39CdrDraftService;
use App\Services\Ib39CdrStatusService;
use App\Support\Ib39CdrFormSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CdrController extends Controller
{
    public function show(Ib39CdrProcessing $cdr): Response
    {
        Gate::authorize('view', $cdr);

        return $this->privateView('ib39.cdr.show', ['cdr' => $this->loadWorkspace($cdr)]);
    }

    public function start(
        StartCdrProcessingRequest $request,
        Ib39CdrProcessing $cdr,
        Ib39CdrStatusService $statuses,
    ): RedirectResponse {
        $statuses->start($cdr, $request->user(), $request->ip(), $request->userAgent());

        return redirect()->route('ib39.cdr.edit', $cdr)->with('status', 'CDR processing started.');
    }

    public function edit(Ib39CdrProcessing $cdr): Response
    {
        Gate::authorize('updateDraft', $cdr);

        return $this->privateView('ib39.cdr.edit', [
            'cdr' => $this->loadWorkspace($cdr),
            'sections' => Ib39CdrFormSchema::sections(),
            'repeatableSections' => Ib39CdrFormSchema::repeatableSections(),
            'orderedBlocks' => Ib39CdrFormSchema::orderedBlocks(),
            'defaultContent' => Ib39CdrFormSchema::defaultContent(),
        ]);
    }

    public function update(
        SaveCdrDraftRequest $request,
        Ib39CdrProcessing $cdr,
        Ib39CdrDraftService $drafts,
    ): RedirectResponse {
        $drafts->save($cdr, $request->validatedContent(), $request->user(), $request->ip(), $request->userAgent());

        return redirect()->route('ib39.cdr.edit', $cdr)->with('status', 'CDR draft saved.');
    }

    public function preview(Ib39CdrProcessing $cdr): Response
    {
        Gate::authorize('previewDraft', $cdr);

        return $this->document($cdr, false);
    }

    public function print(Ib39CdrProcessing $cdr): Response
    {
        Gate::authorize('printDraft', $cdr);

        return $this->document($cdr, true);
    }

    private function document(Ib39CdrProcessing $cdr, bool $autoPrint): Response
    {
        $cdr = $this->loadWorkspace($cdr);

        return $this->privateView('ib39.cdr.document', [
            'cdr' => $cdr,
            'content' => array_replace(Ib39CdrFormSchema::defaultContent(), $cdr->form?->content ?? []),
            'sections' => Ib39CdrFormSchema::sections(),
            'repeatableSections' => Ib39CdrFormSchema::repeatableSections(),
            'orderedBlocks' => Ib39CdrFormSchema::orderedBlocks(),
            'autoPrint' => $autoPrint,
        ]);
    }

    private function loadWorkspace(Ib39CdrProcessing $cdr): Ib39CdrProcessing
    {
        return $cdr->load([
            'surfacedFormerRebel',
            'form.lastEditor',
            'photos.currentVersion',
            'statusHistories' => fn ($query) => $query->latest(),
        ]);
    }

    private function privateView(string $view, array $data): Response
    {
        return response()->view($view, $data)->withHeaders([
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
