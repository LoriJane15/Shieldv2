<?php

namespace App\Policies;

use App\Models\RcspBarangay;
use App\Models\User;

class RcspBarangayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'afp'
            || ($user->role === 'lgu' && $user->municipality_id !== null);
    }

    public function view(User $user, RcspBarangay $barangay): bool
    {
        return in_array($user->role, ['admin', 'afp'], true)
            || ($user->role === 'lgu' && $user->municipality_id !== null
                && $user->municipality_id === $barangay->municipality_id);
    }

    public function create(User $user): bool
    {
        return $user->role === 'lgu' && $user->municipality_id !== null;
    }

    public function update(User $user, RcspBarangay $barangay): bool
    {
        return $user->role === 'lgu' && $user->municipality_id !== null
            && $user->municipality_id === $barangay->municipality_id
            && $barangay->status !== 'Completed';
    }

    public function review(User $user, RcspBarangay $barangay): bool
    {
        return $user->role === 'admin' && $barangay->status !== 'Completed';
    }

    public function delete(User $user, RcspBarangay $barangay): bool
    {
        return $this->update($user, $barangay) && ! $barangay->forms()->exists();
    }
}
