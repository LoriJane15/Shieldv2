<?php

namespace App\Services;

use App\Models\RcspActivity;
use App\Models\RcspBarangay;
use App\Models\RcspForm;
use App\Models\RcspPhase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RcspWorkflowService
{
    public function createBarangay(int $barangayId, User $user, ?string $catalogKey = null): RcspBarangay
    {
        return DB::transaction(function () use ($barangayId, $user, $catalogKey) {
            $record = RcspBarangay::create(['barangay_id' => $barangayId, 'municipality_id' => $user->municipality_id,
                'status' => 'Pending', 'current_phase' => 0, 'catalog_key' => $catalogKey]);
            $record->phaseStatus()->create([]);

            return $record;
        });
    }

    public function submitPhase(RcspBarangay $barangay, RcspPhase $phase, User $user, array $conduct, array $evidence = []): void
    {
        DB::transaction(function () use ($barangay, $phase, $user, $conduct, $evidence): void {
            foreach (RcspActivity::where('rcsp_phase_id', $phase->id)->orderBy('id')->get() as $activity) {
                $form = RcspForm::where('rcsp_barangay_id', $barangay->id)->where('rcsp_phase_id', $phase->id)
                    ->where('rcsp_activity_id', $activity->id)->latest('id')->first();
                if ($form?->status === 'approved') {
                    continue;
                }
                $file = $form?->file;
                if (($evidence[$activity->id] ?? null) instanceof UploadedFile) {
                    $path = $evidence[$activity->id]->store("rcsp/{$barangay->id}", 'local');
                    $file = 'private:'.$path;
                }
                $payload = ['lgu_user_id' => $user->id, 'conduct' => $conduct[$activity->id], 'file' => $file,
                    'status' => 'submitted', 'reviewed_by_user_id' => null, 'reviewed_at' => null];
                $form ? $form->update($payload) : RcspForm::create($payload + ['rcsp_barangay_id' => $barangay->id,
                    'rcsp_phase_id' => $phase->id, 'rcsp_activity_id' => $activity->id]);
            }
            if ($barangay->status === 'Pending') {
                $barangay->update(['status' => 'Ongoing']);
            }
        });
    }

    public function reviewPhase(RcspBarangay $barangay, RcspPhase $phase, User $reviewer, array $statuses, array $remarks): void
    {
        DB::transaction(function () use ($barangay, $phase, $reviewer, $statuses, $remarks): void {
            $forms = RcspForm::where('rcsp_barangay_id', $barangay->id)->where('rcsp_phase_id', $phase->id)
                ->whereIn('id', array_keys($statuses))->get()->keyBy('id');
            foreach ($statuses as $id => $status) {
                $forms[$id]->update(['status' => $status, 'remarks' => trim((string) ($remarks[$id] ?? '')) ?: null,
                    'reviewed_by_user_id' => $reviewer->id, 'reviewed_at' => now()]);
            }
        });
    }

    public function advance(RcspBarangay $barangay): void
    {
        DB::transaction(function () use ($barangay): void {
            $phase = RcspPhase::where('catalog_key', $barangay->catalog_key)->where('number', $barangay->current_phase)->first();
            $activityIds = $phase ? RcspActivity::where('rcsp_phase_id', $phase->id)->pluck('id') : collect();
            $approved = $phase ? RcspForm::where('rcsp_barangay_id', $barangay->id)->where('rcsp_phase_id', $phase->id)
                ->whereIn('rcsp_activity_id', $activityIds)->where('status', 'approved')->distinct()->count('rcsp_activity_id') : 0;
            if ($activityIds->isEmpty() || $approved !== $activityIds->count()) {
                throw ValidationException::withMessages(['phase' => 'All current-phase activities must be approved before proceeding.']);
            }
            $number = $barangay->current_phase;
            $barangay->phaseStatus()->updateOrCreate([], ["phase{$number}_completed" => true]);
            $number >= 5 ? $barangay->update(['status' => 'Completed']) : $barangay->update(['current_phase' => $number + 1]);
        });
    }
}
