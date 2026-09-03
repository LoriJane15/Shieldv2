<?php

namespace App\Policies;

use App\Enums\Ib39FeaDocumentStatus;
use App\Models\Ib39FeaDocument;
use App\Models\Ib39FeaProcessing;
use App\Models\User;

class Ib39FeaDocumentPolicy
{
    public function start(User $user, Ib39FeaDocument $document, Ib39FeaProcessing $processing): bool
    {
        return $this->hasAccess($user, $document, $processing)
            && $document->status !== Ib39FeaDocumentStatus::Completed;
    }

    public function updatePreliminary(User $user, Ib39FeaDocument $document, Ib39FeaProcessing $processing): bool
    {
        return $this->start($user, $document, $processing);
    }

    public function viewHistory(User $user, Ib39FeaDocument $document, Ib39FeaProcessing $processing): bool
    {
        return $this->hasAccess($user, $document, $processing);
    }

    public function editDraft(User $user, Ib39FeaDocument $document, Ib39FeaProcessing $processing): bool
    {
        return $document->document_type->hasDraftEditor()
            && $this->start($user, $document, $processing);
    }

    public function viewDraft(User $user, Ib39FeaDocument $document, Ib39FeaProcessing $processing): bool
    {
        return $document->document_type->hasDraftEditor()
            && $this->start($user, $document, $processing);
    }

    public function uploadDraft(User $user, Ib39FeaDocument $document, Ib39FeaProcessing $processing): bool
    {
        return $this->start($user, $document, $processing)
            && (bool) $processing->surfacedFormerRebel()->value('possessed_firearms');
    }

    public function viewUploads(User $user, Ib39FeaDocument $document, Ib39FeaProcessing $processing): bool
    {
        return $this->hasAccess($user, $document, $processing);
    }

    private function hasAccess(User $user, Ib39FeaDocument $document, Ib39FeaProcessing $processing): bool
    {
        return $user->is_active
            && $user->hasRole('39th_ib')
            && $document->fea_processing_id === $processing->id
            && $processing->surfacedFormerRebel()->exists();
    }
}
