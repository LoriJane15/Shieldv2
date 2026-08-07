<?php

namespace App\Policies;

use App\Models\EclipWorkflowActivity;
use App\Models\User;

class EclipWorkflowActivityPolicy
{
    public function updateWorkflowActivity(User $user, EclipWorkflowActivity $activity): bool
    {
        $case = $activity->eclipCase;
        $scopedRoles = ['lswdo', 'dilg_provincial_focal', 'local_eclip_committee', 'pnp'];
        $hasScope = ! in_array($user->role, $scopedRoles, true)
            || ($user->municipality_id !== null && $user->municipality_id === $case->municipality_id);

        return $hasScope
            && in_array($user->role, $activity->responsible_roles ?? [], true)
            && in_array($activity->status, ['pending', 'ongoing', 'late'], true);
    }
}
