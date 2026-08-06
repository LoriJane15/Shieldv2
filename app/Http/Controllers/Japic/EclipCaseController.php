<?php

namespace App\Http\Controllers\Japic;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Japic\ReviewEclipDocumentRequest;
use App\Models\EclipCase;
use App\Models\EclipDocument;
use App\Models\EclipDocumentRequirement;
use App\Services\EclipDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EclipCaseController extends Controller
{
    public function index(): View
    {
        $cases = EclipCase::query()
            ->whereIn('status', [
                EclipCaseStatus::DocumentProcessing->value,
                EclipCaseStatus::DocumentsIncomplete->value,
                EclipCaseStatus::DocumentsCertified->value,
            ])
            ->with('formerRebel')
            ->latest('updated_at')
            ->paginate(15);

        return view('japic.eclip.index', ['cases' => $cases]);
    }

    public function show(EclipCase $eclipCase): View
    {
        $this->authorize('view', $eclipCase);
        $eclipCase->load([
            'formerRebel.municipality',
            'documents.requirement',
            'documents.latestVersion.uploader',
            'documents.versions.uploader',
            'documents.reviews.reviewer',
            'statusHistories.user',
        ]);

        return view('japic.eclip.show', [
            'case' => $eclipCase,
            'requirements' => EclipDocumentRequirement::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function review(
        ReviewEclipDocumentRequest $request,
        EclipDocument $document,
        EclipDocumentService $documents,
    ): RedirectResponse {
        $documents->review(
            $document,
            $request->user(),
            $request->validated('decision'),
            $request->validated('remarks'),
            $request->ip(),
        );

        return back()->with('success', 'Document review recorded.');
    }
}
