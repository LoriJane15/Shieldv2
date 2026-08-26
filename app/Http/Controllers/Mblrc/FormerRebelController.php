<?php

namespace App\Http\Controllers\Mblrc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mblrc\SaveFormerRebelDraftRequest;
use App\Http\Requests\Mblrc\StoreFormerRebelRequest;
use App\Http\Requests\Mblrc\UpdateFormerRebelRequest;
use App\Models\Barangay;
use App\Models\FormerRebel;
use App\Models\FormerRebelRegistrationDraft;
use App\Models\Municipality;
use App\Services\FormerRebelRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FormerRebelController extends Controller
{
    /** Monitoring list with search + status filter. */
    public function index(Request $request): View
    {
        $frs = FormerRebel::query()
            ->with(['barangay', 'municipality', 'programStatus'])
            ->withCount(['educationWorks', 'locationHistories', 'skills', 'assistances'])
            ->when($request->search, function ($q, $search) {
                $q->where(fn ($w) => $w
                    ->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('classified_id', 'like', "%{$search}%"));
            })
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('mblrc.fr.index', [
            'frs' => $frs,
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(Request $request): Response
    {
        $draft = FormerRebelRegistrationDraft::query()
            ->where('user_id', $request->user()->id)
            ->first();
        $draftPayload = $draft?->payload ?? [];
        $municipalityId = old('municipality_id', $draftPayload['municipality_id'] ?? null);

        return response()->view('mblrc.fr.create', [
            'municipalities' => Municipality::orderBy('name')->get(),
            'barangays' => $municipalityId
                ? Barangay::query()->where('municipality_id', $municipalityId)->orderBy('name')->get()
                : collect(),
            'statuses' => $this->statuses(),
            'draft' => $draft,
        ])->withHeaders($this->privateResponseHeaders());
    }

    public function store(
        StoreFormerRebelRequest $request,
        FormerRebelRegistrationService $registrations,
    ): RedirectResponse {
        $fr = $registrations->register(
            $request->validated(),
            $request->user(),
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()->route('mblrc.fr.show', $fr)
            ->with('success', "Former Rebel {$fr->classified_id} registered.");
    }

    public function saveDraft(
        SaveFormerRebelDraftRequest $request,
        FormerRebelRegistrationService $registrations,
    ): JsonResponse {
        $data = $request->safe()->except('autosave');
        $draft = $registrations->saveDraft(
            $data,
            $request->user(),
            ! $request->boolean('autosave'),
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Draft saved securely.',
            'saved_at' => $draft->saved_at?->toIso8601String(),
        ])->withHeaders($this->privateResponseHeaders());
    }

    public function show(FormerRebel $formerRebel): View
    {
        $formerRebel->load([
            'barangay', 'municipality', 'programStatus',
            'skills', 'assistances', 'educationWorks', 'locationHistories',
        ]);

        return view('mblrc.fr.show', [
            'fr' => $formerRebel,
            'education' => $formerRebel->educationWorks->sortByDesc('updated_at')->first(),
        ]);
    }

    public function edit(FormerRebel $formerRebel): View
    {
        return view('mblrc.fr.edit', [
            'fr' => $formerRebel,
            'municipalities' => Municipality::orderBy('name')->get(),
            'barangays' => Barangay::where('municipality_id', $formerRebel->municipality_id)->orderBy('name')->get(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(UpdateFormerRebelRequest $request, FormerRebel $formerRebel): RedirectResponse
    {
        $data = $request->validated();
        $formerRebel->update($data);

        return redirect()->route('mblrc.fr.show', $formerRebel)
            ->with('success', 'Profile updated.');
    }

    public function destroy(FormerRebel $formerRebel): RedirectResponse
    {
        if ($formerRebel->hasRecordedHistory()) {
            abort(422, 'Former Rebel records with monitoring history cannot be deleted.');
        }

        $id = $formerRebel->classified_id;
        $formerRebel->delete();

        return redirect()->route('mblrc.fr.index')
            ->with('success', "Former Rebel {$id} deleted.");
    }

    /** Map markers — all FRs with coordinates. */
    public function locations(): JsonResponse
    {
        $rows = FormerRebel::query()
            ->with(['municipality:id,name', 'programStatus:id,former_rebel_id,reintegration_status'])
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->get([
                'id', 'classified_id', 'municipality_id', 'placement_address', 'latitude', 'longitude',
                'status', 'batch_year', 'occupation', 'updated_at',
            ])
            ->map(fn ($fr) => [
                'id' => $fr->id,
                'classified_id' => $fr->classified_id,
                'address' => $fr->placement_address,
                'lat' => (float) $fr->latitude,
                'lng' => (float) $fr->longitude,
                'status' => $fr->status,
                'program_status' => $fr->programStatus?->reintegration_status ?? 'Not-Started',
                'municipality' => $fr->municipality?->name ?? 'Not assigned',
                'batch' => $fr->batch_year,
                'occupation' => $fr->occupation,
                'last_updated' => $fr->updated_at?->timezone(config('app.display_timezone'))->format('M d, Y'),
                'url' => route('mblrc.fr.show', $fr->id),
            ]);

        return response()->json($rows)->withHeaders($this->privateResponseHeaders());
    }

    /** Cascade: barangays for a municipality. */
    public function barangays(Request $request): JsonResponse
    {
        $request->validate(['municipality_id' => 'required|exists:municipalities,id']);

        return response()->json(
            Barangay::where('municipality_id', $request->municipality_id)
                ->orderBy('name')->get(['id', 'name'])
        )->withHeaders($this->privateResponseHeaders());
    }

    private function statuses(): array
    {
        return [
            'Active', 'On hold', 'Reintegrated', 'Inactive', 'Under Review',
            'Disengaged', 'Pending', 'Suspended', 'Completed', 'Deceased', 'Relocated',
        ];
    }

    private function privateResponseHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
        ];
    }
}
