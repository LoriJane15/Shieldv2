<?php

namespace App\Policies;

use App\Models\EclipBasicService;
use App\Models\User;

class EclipBasicServicePolicy
{
    public function view(User $user, EclipBasicService $service): bool
    {
        if ($user->hasRole('admin', 'super_admin')) {
            return true;
        }

        if ($user->hasRole('lswdo')) {
            return $user->municipality_id !== null && $user->municipality_id === $service->eclipCase->municipality_id;
        }

        return $user->hasRole('gov_agency')
            && $user->gov_agency_id !== null
            && $user->gov_agency_id === $service->gov_agency_id;
    }

    public function update(User $user, EclipBasicService $service): bool
    {
        return $this->view($user, $service) && $user->hasRole('lswdo', 'gov_agency');
    }

    public function uploadDocument(User $user, EclipBasicService $service): bool
    {
        return $this->update($user, $service);
    }

    public function downloadDocument(User $user, EclipBasicService $service): bool
    {
        return $this->view($user, $service);
    }
}
