<?php

namespace App\Policies;

use App\Enums\Ib39CdrDocumentSource;
use App\Models\Ib39CdrDocumentVersion;
use App\Models\User;

class Ib39CdrDocumentVersionPolicy
{
    public function preview(User $user, Ib39CdrDocumentVersion $version): bool
    {
        return $this->hasAccess($user, $version);
    }

    public function download(User $user, Ib39CdrDocumentVersion $version): bool
    {
        return $this->hasAccess($user, $version)
            && $version->source_type === Ib39CdrDocumentSource::Uploaded;
    }

    public function print(User $user, Ib39CdrDocumentVersion $version): bool
    {
        return $this->hasAccess($user, $version)
            && $version->source_type === Ib39CdrDocumentSource::Generated;
    }

    private function hasAccess(User $user, Ib39CdrDocumentVersion $version): bool
    {
        return $user->is_active
            && $user->hasRole('39th_ib')
            && $version->processing()->whereHas('surfacedFormerRebel')->exists();
    }
}
