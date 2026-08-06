<?php

namespace App\Services;

use App\Enums\EclipCaseStatus;
use App\Models\EclipCase;
use App\Models\EclipFundTransaction;
use App\Models\User;
use App\Notifications\EclipCaseActionNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class EclipFundingService
{
    public function __construct(
        private readonly EclipCaseWorkflowService $workflow,
        private readonly EclipFundProofStorageService $proofs,
    ) {}

    public function record(EclipCase $case, array $data, ?UploadedFile $proof, User $actor, ?string $ipAddress): EclipFundTransaction
    {
        $proofPath = null;
        $proofMetadata = [];
        if ($proof) {
            $safeName = basename(str_replace('\\', '/', $proof->getClientOriginalName()));
            $safeName = preg_replace('/[^\pL\pN._ -]/u', '_', $safeName) ?: 'proof.'.strtolower($proof->extension() ?: 'bin');
            $proofMetadata = [
                'proof_original_name' => $safeName,
                'proof_mime_type' => $proof->getMimeType() ?: 'application/octet-stream',
                'proof_size_bytes' => $proof->getSize(),
                'proof_sha256' => hash_file('sha256', $proof->getRealPath()),
            ];
            $proofPath = $this->proofs->store($proof, $case);
        }

        try {
            $transaction = DB::transaction(function () use ($case, $data, $proofPath, $proofMetadata, $actor, $ipAddress) {
                $lockedCase = EclipCase::query()->lockForUpdate()->findOrFail($case->id);
                $approvedReview = $lockedCase->dilgReviews()->where('decision', 'approved')->latest('reviewed_at')->firstOrFail();
                $revision = $approvedReview->revision()->firstOrFail();
                $approvedCents = $this->moneyToCents($revision->assessed_amount);
                $type = $data['type'];

                if ($type === 'allocation' && ! in_array($lockedCase->status, [EclipCaseStatus::Approved, EclipCaseStatus::FundAllocationPending], true)) {
                    throw ValidationException::withMessages(['type' => 'Allocations can only be recorded for an approved case awaiting allocation.']);
                }
                if ($type === 'transfer' && $lockedCase->status !== EclipCaseStatus::FundsAllocated) {
                    throw ValidationException::withMessages(['type' => 'Transfers can only be recorded after the approved amount is fully allocated.']);
                }

                $existingCents = $this->moneyToCents($lockedCase->fundTransactions()->where('type', $type)->sum('amount'));
                $newTotalCents = $existingCents + $this->moneyToCents($data['amount']);
                $limitCents = $type === 'allocation'
                    ? $approvedCents
                    : $this->moneyToCents($lockedCase->fundTransactions()->where('type', 'allocation')->sum('amount'));

                if ($newTotalCents > $limitCents) {
                    throw ValidationException::withMessages(['amount' => 'The cumulative amount exceeds the authorized funding limit.']);
                }

                $transaction = $lockedCase->fundTransactions()->create([
                    ...$data,
                    'assistance_request_id' => $approvedReview->assistance_request_id,
                    'assistance_revision_id' => $revision->id,
                    'proof_path' => $proofPath,
                    ...$proofMetadata,
                    'created_by' => $actor->id,
                ]);

                if ($type === 'allocation') {
                    $lockedCase = $this->workflow->beginFundAllocation($lockedCase, $actor, $ipAddress);
                    if ($newTotalCents === $approvedCents) {
                        $this->workflow->markFundsAllocated($lockedCase, $actor, $ipAddress);
                    }
                } elseif ($newTotalCents === $limitCents) {
                    $this->workflow->markFundsTransferred($lockedCase, $actor, $ipAddress);
                }

                return $transaction;
            });
        } catch (\Throwable $exception) {
            $this->proofs->delete($proofPath);
            throw $exception;
        }

        $updatedCase = $case->fresh();
        if ($updatedCase->status === EclipCaseStatus::FundsTransferred) {
            Notification::send(
                User::query()->where('role', 'local_eclip_committee')->where('municipality_id', $updatedCase->municipality_id)->get(),
                new EclipCaseActionNotification($updatedCase, 'Transferred E-CLIP assistance is ready for release.', 'local_eclip.cases.show'),
            );
        }

        return $transaction;
    }

    private function moneyToCents(string|int|float|null $amount): int
    {
        $normalized = number_format((float) ($amount ?? 0), 2, '.', '');
        [$whole, $fraction] = explode('.', $normalized);

        return ((int) $whole * 100) + (int) $fraction;
    }
}
