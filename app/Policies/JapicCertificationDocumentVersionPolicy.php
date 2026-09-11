<?php

namespace App\Policies;

use App\Enums\JapicCertificationStatus;
use App\Models\JapicCertificationDocumentVersion;
use App\Models\JapicCertificationProcessing;
use App\Models\User;

class JapicCertificationDocumentVersionPolicy
{
    public function preview(User $user, JapicCertificationDocumentVersion $version): bool
    {
        return $this->hasAccess($user, $version);
    }

    public function download(User $user, JapicCertificationDocumentVersion $version): bool
    {
        return $this->hasAccess($user, $version);
    }

    private function hasAccess(User $user, JapicCertificationDocumentVersion $version): bool
    {
        $processing = $version->processing;

        return $processing instanceof JapicCertificationProcessing
            && $processing->status === JapicCertificationStatus::Completed
            && $processing->current_final_version_id === $version->id
            && app(JapicCertificationProcessingPolicy::class)->view($user, $processing);
    }
}
