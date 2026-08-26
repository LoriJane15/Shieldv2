<?php

namespace App\Services;

use App\Enums\EclipCaseStatus;
use App\Models\EclipAssistanceRelease;
use App\Models\EclipCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EclipAssistanceReleaseService
{
    public function __construct(
        private readonly EclipCaseWorkflowService $workflow,
        private readonly EclipOfficialWorkflowService $officialWorkflow,
        private readonly EclipReleaseAcknowledgmentStorageService $acknowledgments,
    ) {}

    public function record(EclipCase $case, array $data, UploadedFile $file, User $actor, ?string $ipAddress): EclipAssistanceRelease
    {
        $safeName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $safeName = preg_replace('/[^\pL\pN._ -]/u', '_', $safeName) ?: 'acknowledgment.'.strtolower($file->extension() ?: 'bin');
        $metadata = [
            'acknowledgment_original_name' => $safeName,
            'acknowledgment_mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'acknowledgment_size_bytes' => $file->getSize(),
            'acknowledgment_sha256' => hash_file('sha256', $file->getRealPath()),
        ];
        $path = $this->acknowledgments->store($file, $case);

        try {
            return DB::transaction(function () use ($case, $data, $path, $metadata, $actor, $ipAddress) {
                $lockedCase = EclipCase::query()->lockForUpdate()->findOrFail($case->id);
                if (! in_array($lockedCase->status, [EclipCaseStatus::FundsTransferred, EclipCaseStatus::ReleasePending], true)) {
                    throw ValidationException::withMessages(['status' => 'This case is not ready for assistance release.']);
                }

                $step7A = $lockedCase->workflowActivities()->where('step_code', '7A')->first();
                if ($step7A && $step7A->status !== 'completed') {
                    throw ValidationException::withMessages(['status' => 'Complete Step 7A check processing before recording an assistance release.']);
                }

                $approvedReview = $lockedCase->dilgReviews()->where('decision', 'approved')->latest('reviewed_at')->firstOrFail();
                $transferredCents = $this->moneyToCents($lockedCase->fundTransactions()->where('type', 'transfer')->sum('amount'));
                $releasedCents = $this->moneyToCents($lockedCase->assistanceReleases()->sum('amount'));
                $newTotalCents = $releasedCents + $this->moneyToCents($data['amount']);

                if ($transferredCents <= 0 || $newTotalCents > $transferredCents) {
                    throw ValidationException::withMessages(['amount' => 'The cumulative release exceeds the transferred amount.']);
                }

                $release = $lockedCase->assistanceReleases()->create([
                    ...$data,
                    'assistance_request_id' => $approvedReview->assistance_request_id,
                    'assistance_revision_id' => $approvedReview->assistance_revision_id,
                    'acknowledgment_path' => $path,
                    ...$metadata,
                    'released_by' => $actor->id,
                ]);

                $revision = $approvedReview->revision()->with('category')->firstOrFail();
                $lockedCase->formerRebel->assistances()->updateOrCreate(
                    ['source_type' => 'eclip_release', 'source_id' => $release->id],
                    [
                        'assistance_type' => $revision->category?->name ?? 'E-CLIP Assistance',
                        'amount_or_value' => $release->amount,
                        'provider' => 'Local E-CLIP Committee / LSWDO',
                        'date_received' => $release->released_at,
                        'status' => 'Completed',
                        'remarks' => $release->remarks,
                    ],
                );

                $lockedCase = $this->workflow->beginAssistanceRelease($lockedCase, $actor, $ipAddress);
                $this->officialWorkflow->recordDomainEvent(
                    $lockedCase,
                    '7B',
                    $actor,
                    'assistance_release_recorded',
                    $release->remarks,
                    [
                        'release_id' => $release->id,
                        'release_reference' => $release->release_reference,
                        'recipient' => $release->recipient,
                        'amount' => $release->amount,
                        'released_at' => $release->released_at?->toDateString(),
                    ],
                    $ipAddress,
                );
                if ($newTotalCents === $transferredCents) {
                    $this->workflow->completeAssistanceRelease($lockedCase, $actor, $ipAddress);
                }

                return $release;
            });
        } catch (\Throwable $exception) {
            $this->acknowledgments->delete($path);
            throw $exception;
        }
    }

    public function confirmReceived(EclipAssistanceRelease $release, User $actor, ?string $remarks, ?string $ipAddress): EclipAssistanceRelease
    {
        return DB::transaction(function () use ($release, $actor, $remarks, $ipAddress) {
            $locked = EclipAssistanceRelease::query()->with('eclipCase')->lockForUpdate()->findOrFail($release->id);
            if ($locked->received_confirmed_at) {
                return $locked;
            }

            $locked->update([
                'received_confirmed_at' => now(),
                'received_confirmed_by' => $actor->id,
                'remarks' => $remarks ?: $locked->remarks,
            ]);

            $case = $locked->eclipCase;
            $allConfirmed = $case->assistanceReleases()->exists()
                && ! $case->assistanceReleases()->whereNull('received_confirmed_at')->exists();
            if ($allConfirmed) {
                $this->officialWorkflow->transitionDomainActivity(
                    $case,
                    '7B',
                    $actor,
                    'completed',
                    'beneficiary_receipt_confirmed',
                    $remarks ?: 'LSWDO confirmed receipt of all released assistance by the FR/FVE.',
                    ['confirmed_release_id' => $locked->id, 'received_confirmed_at' => now()->toIso8601String()],
                    $ipAddress,
                );
            } else {
                $this->officialWorkflow->recordDomainEvent(
                    $case,
                    '7B',
                    $actor,
                    'beneficiary_receipt_confirmed',
                    $remarks,
                    ['confirmed_release_id' => $locked->id, 'received_confirmed_at' => now()->toIso8601String()],
                    $ipAddress,
                );
            }

            return $locked->fresh();
        });
    }

    private function moneyToCents(string|int|float|null $amount): int
    {
        $normalized = number_format((float) ($amount ?? 0), 2, '.', '');
        [$whole, $fraction] = explode('.', $normalized);

        return ((int) $whole * 100) + (int) $fraction;
    }
}
