<?php

namespace App\Http\Controllers\Lswdo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lswdo\ConfirmAssistanceReceiptRequest;
use App\Models\EclipAssistanceRelease;
use App\Services\EclipAssistanceReleaseService;
use Illuminate\Http\RedirectResponse;

class AssistanceReceiptController extends Controller
{
    public function store(
        ConfirmAssistanceReceiptRequest $request,
        EclipAssistanceRelease $release,
        EclipAssistanceReleaseService $service,
    ): RedirectResponse {
        $service->confirmReceived($release, $request->user(), $request->validated('remarks'), $request->ip());

        return back()->with('success', 'Beneficiary receipt was confirmed and recorded in Step 7B history.');
    }
}
