<?php

namespace App\Models;

use App\Enums\Ib39FeaComplianceStatus;
use App\Enums\Ib39FeaDocumentStatus;
use App\Enums\Ib39FeaOverallStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ib39FeaProcessing extends Model
{
    public const PSWDO_ACCESS_MESSAGE = 'No securely linked PSWDO enrollment is available. Final FEA processing is disabled.';

    protected $guarded = ['id', 'ib39_surfaced_former_rebel_id'];

    public function surfacedFormerRebel(): BelongsTo
    {
        return $this->belongsTo(Ib39SurfacedFormerRebel::class, 'ib39_surfaced_former_rebel_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Ib39FeaDocument::class, 'fea_processing_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(Ib39FeaProcessingHistory::class, 'fea_processing_id');
    }

    public function overallStatus(): Ib39FeaOverallStatus
    {
        $record = $this->surfacedFormerRebel;

        if (! $record || ! $record->possessed_firearms) {
            return Ib39FeaOverallStatus::NotApplicable;
        }

        // Stage 1 deliberately has no inferred or manually entered PSWDO link.
        return Ib39FeaOverallStatus::AwaitingPswdoEnrollment;
    }

    public function statusFromDocuments(): Ib39FeaOverallStatus
    {
        $documents = $this->relationLoaded('documents') ? $this->documents : $this->documents()->get();
        $required = $documents->where('is_required', true);

        if ($required->contains(fn (Ib39FeaDocument $document) => $document->compliance_status !== Ib39FeaComplianceStatus::None)) {
            return Ib39FeaOverallStatus::ForCompliance;
        }

        if ($required->isNotEmpty() && $required->every(fn (Ib39FeaDocument $document) => $document->status === Ib39FeaDocumentStatus::Completed)) {
            return Ib39FeaOverallStatus::Completed;
        }

        if ($documents->contains(fn (Ib39FeaDocument $document) => $document->status === Ib39FeaDocumentStatus::Processing)) {
            return Ib39FeaOverallStatus::Processing;
        }

        if ($required->contains(fn (Ib39FeaDocument $document) => $document->status === Ib39FeaDocumentStatus::Pending)) {
            return Ib39FeaOverallStatus::Pending;
        }

        return Ib39FeaOverallStatus::ReadyForProcessing;
    }
}
