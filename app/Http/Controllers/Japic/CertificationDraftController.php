<?php

namespace App\Http\Controllers\Japic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Japic\SaveCertificationDraftRequest;
use App\Models\JapicCertificationProcessing;
use App\Services\JapicCertificationDraftService;
use App\Support\JapicCertificationDraftSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CertificationDraftController extends Controller
{
    public function edit(JapicCertificationProcessing $japicCertificationProcessing, JapicCertificationDraftSchema $schema): View
    {
        Gate::authorize('editDraft', $japicCertificationProcessing);
        $japicCertificationProcessing->load(['draft.lastSavedBy', 'draftHistories.savedBy', 'surfacedFormerRebel.cancellation']);

        return view('japic.certifications.edit', ['processing' => $japicCertificationProcessing,
            'payload' => $japicCertificationProcessing->draft?->payload ?? $schema->normalize([], $schema->sourceSnapshot($japicCertificationProcessing)),
            'positions' => JapicCertificationDraftSchema::POSITIONS]);
    }

    public function update(SaveCertificationDraftRequest $request, JapicCertificationProcessing $japicCertificationProcessing, JapicCertificationDraftService $drafts): RedirectResponse
    {
        $draft = $drafts->save($japicCertificationProcessing, $request->manualPayload(), $request->validated('control_number'),
            (int) $request->validated('revision'), (int) $request->validated('lock_version'), $request->validated('delay_reason'), $request->user());

        return redirect()->route('japic.certifications.draft.edit', $japicCertificationProcessing)->with('status', 'Draft revision '.$draft->revision.' saved securely.');
    }
}
