<?php

namespace App\Policies;

use App\Models\Ib39SurfacedFormerRebel;
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

    public function view(User $user, Ib39SurfacedFormerRebel $record): bool
    {
        return $user->is_active && $user->hasRole('39th_ib');
    }

    public function cancel(User $user, Ib39SurfacedFormerRebel $record): bool
    {
        return $user->is_active
            && $user->hasRole('39th_ib')
            && ! $record->cancellation()->exists();
    }
}
