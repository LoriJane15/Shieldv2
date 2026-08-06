<?php

namespace App\Http\Controllers\GovAgency;

use App\Http\Controllers\Controller;
use App\Models\AgencyImplanResponse;
use App\Models\GovAgency;
use App\Models\Implementation;
use App\Models\RcspBarangay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Government-agency side of the IMPLAN workflow: view assigned plans,
 * accept/reject them, and maintain agenda files + documentation photos.
 */
class ImplanController extends Controller
{
    public function index(): View
    {
        $agencyId = auth()->user()->gov_agency_id;

        $assigned = Implementation::whereJsonContains('agencies', $agencyId)
            ->latest()->get();

        $responses = AgencyImplanResponse::where('gov_agency_id', $agencyId)
            ->pluck('response_status', 'implementation_id');

        $grouped = [
            'pending' => $assigned->filter(fn ($i) => ($responses[$i->id] ?? 'pending') === 'pending'),
            'accepted' => $assigned->filter(fn ($i) => ($responses[$i->id] ?? null) === 'accepted'),
            'rejected' => $assigned->filter(fn ($i) => ($responses[$i->id] ?? null) === 'rejected'),
        ];

        return view('gov_agency.implan.index', [
            'grouped' => $grouped,
            'agency' => auth()->user()->govAgency,
        ]);
    }

    public function show(Implementation $implan): View
    {
        $this->authorizeAssigned($implan);
        $implan->load('files', 'photos');

        $agencyId = auth()->user()->gov_agency_id;
        $response = AgencyImplanResponse::where('gov_agency_id', $agencyId)
            ->where('implementation_id', $implan->id)->first();

        return view('gov_agency.implan.show', [
            'implan' => $implan,
            'response' => $response,
            'areaNames' => RcspBarangay::with('barangay')->whereIn('id', $implan->target_areas ?? [])
                ->get()->map(fn ($r) => $r->barangay?->name ?? "Barangay #{$r->barangay_id}"),
            'agenciesById' => GovAgency::all()->keyBy('id'),
        ]);
    }

    /** Accept or reject an assigned IMPLAN. */
    public function respond(Request $request, Implementation $implan): RedirectResponse
    {
        $this->authorizeAssigned($implan);

        $data = $request->validate([
            'response_status' => ['required', Rule::in(['accepted', 'rejected'])],
            'rejection_reason' => ['required_if:response_status,rejected', 'nullable', 'string'],
        ]);

        $agencyId = auth()->user()->gov_agency_id;

        DB::transaction(function () use ($implan, $agencyId, $data) {
            AgencyImplanResponse::updateOrCreate(
                ['gov_agency_id' => $agencyId, 'implementation_id' => $implan->id],
                [
                    'response_status' => $data['response_status'],
                    'rejection_reason' => $data['rejection_reason'] ?? null,
                ]
            );

            $implan->taggings()->updateOrCreate(
                ['gov_agency_id' => $agencyId],
                [
                    'status' => $data['response_status'] === 'accepted' ? 'Accepted' : 'Rejected',
                    'reason' => $data['rejection_reason'] ?? null,
                ]
            );

            // First acceptance moves the plan into implementation.
            if ($data['response_status'] === 'accepted' && $implan->status === 'not yet started') {
                $implan->update(['status' => 'ongoing']);
            }
        });

        return back()->with('success', 'Response recorded.');
    }

    /** Agency-side plan detail edit (updateImplanGov equivalent). */
    public function update(Request $request, Implementation $implan): RedirectResponse
    {
        $this->authorizeAssigned($implan);

        $implan->update($request->validate([
            'program' => ['nullable', 'string'],
            'beneficiaries' => ['nullable', 'string'],
            'outcome' => ['nullable', 'string'],
            'resources' => ['nullable', 'string'],
            'support' => ['nullable', 'string'],
            'duration' => ['nullable', 'string'],
            'type_gov' => ['nullable', Rule::in(['NGA', 'PGO', 'Development Partner'])],
            'sources' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]));

        return back()->with('success', 'Plan details updated.');
    }

    public function uploadAgenda(Request $request, Implementation $implan): RedirectResponse
    {
        $this->authorizeAssigned($implan);

        $request->validate([
            'file_name' => ['required', 'string', 'max:250'],
            'description' => ['nullable', 'string'],
            'files' => ['required', 'array'],
            'files.*' => ['file', 'mimes:pdf,doc,docx', 'max:25600'],
        ]);

        foreach ($request->file('files') as $file) {
            $implan->files()->create([
                'file_name' => $request->file_name,
                'description' => $request->description,
                'pdf' => $file->store('implan/agenda', 'public'),
            ]);
        }

        return back()->with('success', 'Agenda file(s) uploaded.');
    }

    public function uploadPhoto(Request $request, Implementation $implan): RedirectResponse
    {
        $this->authorizeAssigned($implan);

        $request->validate([
            'photos' => ['required', 'array'],
            'photos.*' => ['image', 'max:25600'],
        ]);

        foreach ($request->file('photos') as $photo) {
            $implan->photos()->create([
                'image' => $photo->store('implan/photos', 'public'),
            ]);
        }

        return back()->with('success', 'Documentation photo(s) uploaded.');
    }

    private function authorizeAssigned(Implementation $implan): void
    {
        $agencyId = auth()->user()->gov_agency_id;
        abort_unless(
            in_array($agencyId, $implan->agencies ?? [], true),
            403,
            'This plan is not assigned to your agency.'
        );
    }
}
