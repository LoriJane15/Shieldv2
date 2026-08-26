<?php

namespace App\Policies;

use App\Models\EclipWorkflowActivity;
use App\Models\User;

class EclipWorkflowActivityPolicy
{
    public function updateWorkflowActivity(User $user, EclipWorkflowActivity $activity): bool
    {
        return $this->hasResponsibleScope($user, $activity)
            && in_array($activity->status, ['pending', 'ongoing', 'late'], true);
    }

    public function uploadWorkflowDocument(User $user, EclipWorkflowActivity $activity): bool
    {
        return $this->hasResponsibleScope($user, $activity)
            && ! empty($activity->required_documents)
            && ! in_array($activity->status, ['locked', 'not_applicable', 'not_eligible', 'previously_assisted', 'not_authenticated'], true);
    }

    private function hasResponsibleScope(User $user, EclipWorkflowActivity $activity): bool
    {
        $case = $activity->eclipCase;
        $responsibleRole = match ($user->role) {
            'eclip_assessor' => 'lswdo',
            'dilg_reviewer' => 'dilg_provincial_focal',
            'eclip_funding_officer' => 'dilg_fms',
            default => $user->role,
        };
        $hasScope = match ($user->role) {
            'lswdo', 'eclip_assessor' => $case->hasActiveParticipant($user, 'case_processor'),
            'japic' => $case->hasActiveParticipant($user, 'authentication_reviewer'),
            'pnp', 'afp' => $case->hasActiveParticipant($user, 'fea_processor'),
            'gov_agency' => $case->hasActiveParticipant($user),
            'dilg_provincial_focal', 'local_eclip_committee', 'dilg_reviewer', 'eclip_funding_officer' => $user->municipality_id !== null
                && $user->municipality_id === $case->municipality_id,
            'dilg_regional', 'nboo_eclip_pmo', 'dilg_fms', 'mblrc' => $user->can('viewWorkflow', $case),
            default => false,
        };

        return $hasScope
            && in_array($responsibleRole, $activity->responsible_roles ?? [], true);
    }
}
