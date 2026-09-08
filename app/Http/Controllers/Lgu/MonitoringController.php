<?php

namespace App\Http\Controllers\Lgu;

use App\Events\RcspCommentPosted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rcsp\AdvanceRcspPhaseRequest;
use App\Http\Requests\Rcsp\StoreRcspCommentRequest;
use App\Http\Requests\Rcsp\SubmitRcspPhaseRequest;
use App\Models\RcspActivity;
use App\Models\RcspBarangay;
use App\Models\RcspForm;
use App\Models\RcspPhase;
use App\Services\RcspWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * RCSP phased monitoring form. A barangay progresses through 6 phases (0-5);
 * each phase has activities the LGU reports on (conduct + evidence file),
 * which the Katuparan admin later approves/disapproves.
 */
class MonitoringController extends Controller
{
    public function show(RcspBarangay $rcspBarangay): View
    {
        Gate::authorize('view', $rcspBarangay);
        $rcspBarangay->load('barangay', 'municipality');

        $phases = RcspPhase::where('catalog_key', $rcspBarangay->catalog_key)->orderBy('number')->get();
        $currentPhase = $phases->firstWhere('number', $rcspBarangay->current_phase) ?? $phases->first();
        abort_unless($currentPhase, 422, 'No phase catalog is available for this RCSP barangay.');

        $activities = RcspActivity::where('rcsp_phase_id', $currentPhase->id)
            ->orderBy('id')->get();

        // latest form row per activity for this barangay + phase (+ its comment thread)
        $forms = RcspForm::where('rcsp_barangay_id', $rcspBarangay->id)
            ->where('rcsp_phase_id', $currentPhase->id)
            ->with('fileComments.user')
            ->get()
            ->groupBy('rcsp_activity_id')
            ->map(fn ($g) => $g->sortByDesc('id')->first());

        // approved phases (all activities approved) for the "View Phases" summary
        $approvedForms = RcspForm::where('rcsp_barangay_id', $rcspBarangay->id)
            ->where('status', 'approved')
            ->with('activity', 'phase')
            ->get()
            ->groupBy('rcsp_phase_id');

        return view('lgu.monitoring.show', compact(
            'rcspBarangay', 'phases', 'currentPhase', 'activities', 'forms', 'approvedForms'
        ));
    }

    public function submit(SubmitRcspPhaseRequest $request, RcspBarangay $rcspBarangay, RcspWorkflowService $workflow): RedirectResponse
    {
        $phase = RcspPhase::findOrFail($request->integer('phase_id'));
        $workflow->submitPhase($rcspBarangay, $phase, $request->user(), $request->validated('conduct'), $request->file('evidence', []));

        return redirect()->route('lgu.monitoring.show', $rcspBarangay)
            ->with('success', 'Phase submitted for review.');
    }

    public function proceed(AdvanceRcspPhaseRequest $request, RcspBarangay $rcspBarangay, RcspWorkflowService $workflow): RedirectResponse
    {
        $phase = $rcspBarangay->current_phase;
        $workflow->advance($rcspBarangay);

        return back()->with('success', $phase >= 5 ? 'RCSP monitoring completed for this barangay.' : 'Advanced to phase '.($phase + 1).'.');
    }

    /** Full-page file viewer with side-by-side comment thread. */
    public function file(RcspForm $form): View
    {
        $form->load(['rcspBarangay.barangay', 'rcspBarangay.municipality', 'phase', 'activity', 'lguUser', 'fileComments.user']);
        Gate::authorize('view', $form);

        return view('lgu.monitoring.file', compact('form'));
    }

    /** LGU posts a comment on a submitted form (two-way thread with the reviewer). */
    public function storeComment(StoreRcspCommentRequest $request, RcspForm $form): JsonResponse
    {
        $data = $request->validated();

        $comment = $form->fileComments()->create([
            'rcsp_phase_id' => $form->rcsp_phase_id,
            'rcsp_activity_id' => $form->rcsp_activity_id,
            'user_id' => $request->user()->id,
            'text' => $data['text'],
        ]);

        broadcast(new RcspCommentPosted($comment))->toOthers();

        return response()->json([
            'success' => true,
            'comment' => [
                'text' => $comment->text,
                'user' => $request->user()->name,
                'role' => $request->user()->role,
                'at' => $comment->created_at->diffForHumans(),
            ],
        ]);
    }

    public function evidence(RcspForm $form): BinaryFileResponse
    {
        Gate::authorize('viewEvidence', $form);
        abort_if(! $form->file || str_contains($form->file, '..') || str_contains($form->file, '\\')
            || str_starts_with($form->file, '/') || preg_match('/^[A-Za-z]:/', $form->file), 404);
        $private = str_starts_with($form->file, 'private:');
        $path = $private ? substr($form->file, 8) : $form->file;
        abort_unless(str_starts_with($path, 'rcsp/'), 404);
        $disk = Storage::disk($private ? 'local' : 'public');
        abort_unless($path && $disk->exists($path), 404);
        $response = response()->file($disk->path($path));
        $response->headers->set('Cache-Control', 'private, no-store, no-cache, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
