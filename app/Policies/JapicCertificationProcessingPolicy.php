<?php

namespace App\Policies;

use App\Models\JapicCertificationProcessing;
use App\Models\User;

class JapicCertificationProcessingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->hasRole('japic');
    }

    public function view(User $user, JapicCertificationProcessing $processing): bool
    {
        return $this->viewAny($user)
            && ($processing->assigned_to === null || $processing->assigned_to === $user->id);
    }

    public function update(User $user, JapicCertificationProcessing $processing): bool
    {
        return $this->view($user, $processing) && $processing->status->isActive();
    }
}
