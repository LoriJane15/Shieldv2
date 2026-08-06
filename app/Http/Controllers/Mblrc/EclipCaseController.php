<?php

namespace App\Http\Controllers\Mblrc;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mblrc\StoreEclipCaseRequest;
use App\Models\EclipCase;
use App\Models\EclipDocumentRequirement;
use App\Models\FormerRebel;
use App\Services\EclipCaseWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EclipCaseController extends Controller
{
    public function index(Request $request): View
    {
        $cases = EclipCase::query()
            ->with(['formerRebel.municipality', 'assignee'])
            ->latest()
            ->paginate(15);

        return view('mblrc.eclip.index', ['cases' => $cases]);
    }

    public function create(): View
    {
        $this->authorize('create', EclipCase::class);

        $formerRebels = FormerRebel::query()
            ->whereDoesntHave('eclipCases', fn ($query) => $query->whereNotIn('status', [
                EclipCaseStatus::Ineligible->value,
                EclipCaseStatus::Completed->value,
                EclipCaseStatus::Cancelled->value,
            ]))
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get(['id', 'classified_id', 'firstname', 'lastname', 'municipality_id']);

        return view('mblrc.eclip.create', ['formerRebels' => $formerRebels]);
    }

    public function store(StoreEclipCaseRequest $request): RedirectResponse
    {
        $formerRebel = FormerRebel::query()->findOrFail($request->integer('former_rebel_id'));

        $case = DB::transaction(function () use ($formerRebel, $request) {
            $hasActiveCase = EclipCase::query()
                ->where('former_rebel_id', $formerRebel->id)
                ->whereNotIn('status', [
                    EclipCaseStatus::Ineligible->value,
                    EclipCaseStatus::Completed->value,
                    EclipCaseStatus::Cancelled->value,
                ])->lockForUpdate()->exists();

            if ($hasActiveCase) {
                throw ValidationException::withMessages([
                    'former_rebel_id' => 'This beneficiary already has an active E-CLIP case.',
                ]);
            }

            $case = EclipCase::query()->create([
                'former_rebel_id' => $formerRebel->id,
                'municipality_id' => $formerRebel->municipality_id,
                'created_by' => $request->user()->id,
                'status' => EclipCaseStatus::Draft,
            ]);
            $case->update(['case_number' => 'ECLIP-'.now()->format('Y').'-'.str_pad((string) $case->id, 6, '0', STR_PAD_LEFT)]);
            $case->statusHistories()->create([
                'user_id' => $request->user()->id,
                'to_status' => EclipCaseStatus::Draft->value,
                'ip_address' => $request->ip(),
            ]);

            return $case;
        });

        return redirect()->route('mblrc.eclip.show', $case)->with('success', 'E-CLIP case created as a draft.');
    }

    public function show(EclipCase $eclipCase): View
    {
        $this->authorize('view', $eclipCase);
        $eclipCase->load([
            'formerRebel.municipality', 'creator', 'assignee', 'eligibilityReviews.reviewer',
            'statusHistories.user', 'documents.requirement', 'documents.versions.uploader', 'documents.latestVersion', 'documents.reviews.reviewer',
        ]);

        return view('mblrc.eclip.show', [
            'case' => $eclipCase,
            'requirements' => EclipDocumentRequirement::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function submit(Request $request, EclipCase $eclipCase, EclipCaseWorkflowService $workflow): RedirectResponse
    {
        $this->authorize('submit', $eclipCase);
        $workflow->submitForEligibility($eclipCase, $request->user(), $request->ip());

        return redirect()->route('mblrc.eclip.show', $eclipCase)->with('success', 'Case submitted for LSWDO eligibility review.');
    }
}
