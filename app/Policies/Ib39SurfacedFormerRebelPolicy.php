<?php

namespace App\Policies;

use App\Models\User;

class Ib39SurfacedFormerRebelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->hasRole('39th_ib');
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->hasRole('39th_ib');
    }
}
