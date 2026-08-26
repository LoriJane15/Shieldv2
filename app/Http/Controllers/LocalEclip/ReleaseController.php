<?php

namespace App\Http\Controllers\LocalEclip;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocalEclip\StoreAssistanceReleaseRequest;
use App\Models\EclipAssistanceRelease;
use App\Models\EclipCase;
use App\Services\EclipAssistanceReleaseService;
use App\Services\EclipReleaseAcknowledgmentStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReleaseController extends Controller
{
    public function index(Request $request): View
    {
        $cases = EclipCase::query()->where('municipality_id', $request->user()->municipality_id)
            ->whereIn('status', [EclipCaseStatus::FundsTransferred->value, EclipCaseStatus::ReleasePending->value, EclipCaseStatus::AssistanceReleased->value, EclipCaseStatus::Completed->value])
            ->with(['formerRebel', 'fundTransactions', 'assistanceReleases'])
            ->latest('updated_at')->paginate(15);

        return view('local_eclip.cases.index', ['cases' => $cases]);
    }

    public function show(EclipCase $eclipCase): View
    {
        $this->authorize('view', $eclipCase);
        $eclipCase->load(['formerRebel.municipality', 'fundTransactions', 'assistanceReleases.releaser']);

        return view('local_eclip.cases.show', ['case' => $eclipCase]);
    }

    public function store(StoreAssistanceReleaseRequest $request, EclipCase $eclipCase, EclipAssistanceReleaseService $releases): RedirectResponse
    {
        $releases->record($eclipCase, $request->safe()->except('acknowledgment'), $request->file('acknowledgment'), $request->user(), $request->ip());

        return back()->with('success', 'Assistance release recorded.');
    }

    public function download(EclipAssistanceRelease $release, EclipReleaseAcknowledgmentStorageService $storage): StreamedResponse
    {
        $this->authorize('downloadReleaseAcknowledgment', $release->eclipCase()->firstOrFail());

        return $storage->download($release->acknowledgment_path, $release->acknowledgment_original_name);
    }
}
