<?php

namespace App\Policies;

use App\Models\Ib39FeaDocumentVersion;
use App\Models\User;

class Ib39FeaDocumentVersionPolicy
{
    public function preview(User $user, Ib39FeaDocumentVersion $version): bool
    {
        return $this->hasAccess($user, $version);
    }

    public function download(User $user, Ib39FeaDocumentVersion $version): bool
    {
        return $this->hasAccess($user, $version);
    }

    private function hasAccess(User $user, Ib39FeaDocumentVersion $version): bool
    {
        return $user->is_active
            && $user->hasRole('39th_ib')
            && $version->processing()->whereHas('surfacedFormerRebel')->exists();
    }
}
