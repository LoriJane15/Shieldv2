<?php

namespace App\Http\Controllers\EclipFunding;

use App\Enums\EclipCaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\EclipFunding\StoreFundTransactionRequest;
use App\Models\EclipCase;
use App\Models\EclipFundTransaction;
use App\Services\EclipFundingService;
use App\Services\EclipFundProofStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FundingController extends Controller
{
    public function index(Request $request): View
    {
        $cases = EclipCase::query()
            ->when($request->user()->hasRole('eclip_funding_officer'), fn ($query) => $query->where('municipality_id', $request->user()->municipality_id))
            ->whereIn('status', [EclipCaseStatus::Approved->value, EclipCaseStatus::FundAllocationPending->value, EclipCaseStatus::FundsAllocated->value, EclipCaseStatus::FundsTransferred->value])
            ->with(['formerRebel', 'dilgReviews' => fn ($query) => $query->where('decision', 'approved')->with('revision')])
            ->latest('updated_at')->paginate(15);

        return view('eclip_funding.cases.index', ['cases' => $cases]);
    }

    public function show(EclipCase $eclipCase): View
    {
        $this->authorize('view', $eclipCase);
        $eclipCase->load(['formerRebel.municipality', 'dilgReviews.revision.category', 'fundTransactions.creator']);

        return view('eclip_funding.cases.show', ['case' => $eclipCase]);
    }

    public function store(StoreFundTransactionRequest $request, EclipCase $eclipCase, EclipFundingService $funding): RedirectResponse
    {
        $funding->record($eclipCase, $request->safe()->except('proof'), $request->file('proof'), $request->user(), $request->ip());

        return back()->with('success', 'Funding transaction recorded.');
    }

    public function download(EclipFundTransaction $transaction, EclipFundProofStorageService $proofs): StreamedResponse
    {
        $this->authorize('downloadFundingProof', $transaction->eclipCase()->firstOrFail());
        abort_unless($transaction->proof_path && $transaction->proof_original_name, 404);

        return $proofs->download($transaction->proof_path, $transaction->proof_original_name);
    }
}
