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

                $lockedCase = $this->workflow->beginAssistanceRelease($lockedCase, $actor, $ipAddress);
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

    private function moneyToCents(string|int|float|null $amount): int
    {
        $normalized = number_format((float) ($amount ?? 0), 2, '.', '');
        [$whole, $fraction] = explode('.', $normalized);

        return ((int) $whole * 100) + (int) $fraction;
    }
}
