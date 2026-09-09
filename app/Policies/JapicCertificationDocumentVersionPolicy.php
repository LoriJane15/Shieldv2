<?php

namespace App\Policies;

use App\Models\JapicCertificationDocumentVersion;
use App\Models\JapicCertificationProcessing;
use App\Models\User;

class JapicCertificationDocumentVersionPolicy
{
    public function view(User $user, JapicCertificationDocumentVersion $version): bool
    {
        $processing = $version->processing;

        return $processing instanceof JapicCertificationProcessing
            && app(JapicCertificationProcessingPolicy::class)->view($user, $processing);
    }
}
