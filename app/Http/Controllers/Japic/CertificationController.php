<?php

namespace App\Http\Controllers\Japic;

use App\Enums\JapicCertificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Japic\IndexCertificationRequest;
use App\Models\JapicCertificationProcessing;
use App\Services\JapicCertificationRevisionHistoryService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CertificationController extends Controller
{
    public function index(IndexCertificationRequest $request): View
    {
        Gate::authorize('viewAny', JapicCertificationProcessing::class);
        $filters = $request->validatedFilters();
        $search = $filters['search'] ?? null;
        $escaped = $search === null ? null : str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search);

        $records = JapicCertificationProcessing::query()
            ->where(fn ($query) => $query->whereNull('assigned_to')->orWhere('assigned_to', $request->user()->id))
            ->with([
                'surfacedFormerRebel.municipality', 'surfacedFormerRebel.barangay',
                'surfacedFormerRebel.cancellation', 'surfacedFormerRebel.cdrProcessing',
            ])
            ->when($escaped, fn ($query) => $query->whereHas('surfacedFormerRebel', fn ($fr) => $fr
                ->where(fn ($match) => $match
                    ->whereRaw("reference_number LIKE ? ESCAPE '\\'", ["%{$escaped}%"])
                    ->orWhereRaw("first_name LIKE ? ESCAPE '\\'", ["%{$escaped}%"])
                    ->orWhereRaw("last_name LIKE ? ESCAPE '\\'", ["%{$escaped}%"]))))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['timing'] ?? null, fn ($query, $timing) => $query->withTiming($timing))
            ->when($filters['received_from'] ?? null, fn ($query, $date) => $query->whereDate('received_at', '>=', $date))
            ->when($filters['received_to'] ?? null, fn ($query, $date) => $query->whereDate('received_at', '<=', $date))
            ->when($filters['due_from'] ?? null, fn ($query, $date) => $query->whereDate('due_at', '>=', $date))
            ->when($filters['due_to'] ?? null, fn ($query, $date) => $query->whereDate('due_at', '<=', $date))
            ->when(array_key_exists('cancelled', $filters), fn ($query) => $filters['cancelled']
                ? $query->whereHas('surfacedFormerRebel.cancellation')
                : $query->whereDoesntHave('surfacedFormerRebel.cancellation'))
            ->orderBy('due_at')->orderBy('id')->paginate(15)->appends($filters);

        return view('japic.certifications.index', [
            'records' => $records,
            'statuses' => JapicCertificationStatus::cases(),
            'filters' => $filters,
        ]);
    }

    public function show(JapicCertificationProcessing $japicCertificationProcessing): View
    {
        Gate::authorize('view', $japicCertificationProcessing);
        $japicCertificationProcessing->load([
            'surfacedFormerRebel.municipality', 'surfacedFormerRebel.barangay',
            'surfacedFormerRebel.cancellation',
            'draft.lastSavedBy',
            'histories' => fn ($query) => $query->with('actor:id,name')->oldest('occurred_at'),
        ]);

        return view('japic.certifications.show', ['processing' => $japicCertificationProcessing]);
    }

    public function cdr(JapicCertificationProcessing $japicCertificationProcessing): View
    {
        Gate::authorize('view', $japicCertificationProcessing);
        $japicCertificationProcessing->load('surfacedFormerRebel.cdrProcessing.currentFinalVersion');

        return view('japic.certifications.records.cdr', ['processing' => $japicCertificationProcessing]);
    }

    public function fea(JapicCertificationProcessing $japicCertificationProcessing): View
    {
        Gate::authorize('view', $japicCertificationProcessing);
        $japicCertificationProcessing->load([
            'surfacedFormerRebel.feaProcessing.documents.currentDraftVersion',
            'surfacedFormerRebel.feaProcessing.documents.currentSupportingPhotoVersion',
            'surfacedFormerRebel.feaProcessing.documents.currentSurrenderedPhotoVersion',
        ]);

        $documents = $japicCertificationProcessing->surfacedFormerRebel->feaProcessing?->documents
            ->filter(fn ($document) => $document->currentDraftVersion || $document->currentSupportingPhotoVersion || $document->currentSurrenderedPhotoVersion)
            ->values() ?? collect();

        return view('japic.certifications.records.fea', [
            'processing' => $japicCertificationProcessing,
            'documents' => $documents,
        ]);
    }

    public function assistance(JapicCertificationProcessing $japicCertificationProcessing): View
    {
        Gate::authorize('view', $japicCertificationProcessing);
        $japicCertificationProcessing->load('surfacedFormerRebel');

        return view('japic.certifications.records.assistance', ['processing' => $japicCertificationProcessing]);
    }

    public function history(
        JapicCertificationProcessing $japicCertificationProcessing,
        JapicCertificationRevisionHistoryService $history,
        ?int $revision = null,
    ): View {
        Gate::authorize('view', $japicCertificationProcessing);

        return view('japic.certifications.history', $history->viewModel($japicCertificationProcessing, $revision));
    }
}
