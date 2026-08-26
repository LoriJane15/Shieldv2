<?php

namespace App\Policies;

use App\Enums\EclipCaseStatus;
use App\Models\EclipCase;
use App\Models\User;

class EclipCasePolicy
{
    public function view(User $user, EclipCase $case): bool
    {
        if ($user->hasRole('admin', 'super_admin')) {
            return false;
        }

        if ($user->hasRole('mblrc')) {
            return $case->created_by === $user->id || $case->hasActiveParticipant($user);
        }

        if ($user->hasRole('japic')) {
            return $case->hasActiveParticipant($user, 'authentication_reviewer') && in_array($case->status, [
                EclipCaseStatus::AuthenticationPending,
                EclipCaseStatus::AuthenticationUnderReview,
                EclipCaseStatus::AuthenticationReturned,
                EclipCaseStatus::Authenticated,
                EclipCaseStatus::NotAuthenticated,
                EclipCaseStatus::DocumentProcessing,
                EclipCaseStatus::DocumentsIncomplete,
                EclipCaseStatus::DocumentsCertified,
            ], true);
        }

        if ($user->hasRole('lswdo', 'eclip_assessor')) {
            return $case->hasActiveParticipant($user);
        }

        if ($user->hasRole('dilg_provincial_focal')) {
            return $user->municipality_id === $case->municipality_id
                && in_array($case->status, [
                    EclipCaseStatus::SubmittedForDilgReview,
                    EclipCaseStatus::ProvincialEndorsed,
                    EclipCaseStatus::Approved,
                    EclipCaseStatus::Rejected,
                    EclipCaseStatus::ReturnedForAssessmentRevision,
                ], true);
        }

        if ($user->hasRole('dilg_regional', 'nboo_eclip_pmo')) {
            $statuses = [
                EclipCaseStatus::ProvincialEndorsed,
                EclipCaseStatus::RegionalEndorsed,
                EclipCaseStatus::Approved,
                EclipCaseStatus::Rejected,
                EclipCaseStatus::ReturnedForAssessmentRevision,
            ];
            if ($user->hasRole('dilg_regional')) {
                $statuses = [...$statuses, EclipCaseStatus::FundsAllocated, EclipCaseStatus::FundsTransferred];
            }

            return in_array($case->status, $statuses, true);
        }

        if ($user->hasRole('dilg_reviewer')) {
            return $user->municipality_id === $case->municipality_id
                && in_array($case->status, [EclipCaseStatus::SubmittedForDilgReview, EclipCaseStatus::Approved, EclipCaseStatus::Rejected, EclipCaseStatus::ReturnedForAssessmentRevision], true);
        }

        if ($user->hasRole('dilg_fms')) {
            return in_array($case->status, [
                EclipCaseStatus::Approved,
                EclipCaseStatus::FundAllocationPending,
                EclipCaseStatus::FundsAllocated,
                EclipCaseStatus::FundsTransferred,
            ], true);
        }

        if ($user->hasRole('eclip_funding_officer')) {
            return $user->municipality_id === $case->municipality_id
                && in_array($case->status, [EclipCaseStatus::Approved, EclipCaseStatus::FundAllocationPending, EclipCaseStatus::FundsAllocated, EclipCaseStatus::FundsTransferred], true);
        }

        if ($user->hasRole('local_eclip_committee')) {
            return $user->municipality_id !== null
                && $user->municipality_id === $case->municipality_id
                && in_array($case->status, [
                    EclipCaseStatus::FundsTransferred,
                    EclipCaseStatus::ReleasePending,
                    EclipCaseStatus::AssistanceReleased,
                    EclipCaseStatus::Completed,
                ], true);
        }

        return false;
    }

    public function viewWorkflow(User $user, EclipCase $case): bool
    {
        if ($user->hasRole('mblrc')) {
            return $case->created_by === $user->id || $case->hasActiveParticipant($user);
        }

        if ($user->hasRole('lswdo', 'eclip_assessor', 'japic', 'pnp', 'afp', 'gov_agency')) {
            return $case->hasActiveParticipant($user);
        }

        if ($user->hasRole('local_eclip_committee')) {
            return $user->municipality_id !== null
                && $user->municipality_id === $case->municipality_id;
        }

        if ($user->hasRole('dilg_provincial_focal', 'dilg_reviewer', 'eclip_funding_officer')) {
            return $user->municipality_id !== null
                && $user->municipality_id === $case->municipality_id
                && $this->hasVisibleRoleActivity($user, $case);
        }

        return $user->hasRole('dilg_regional', 'nboo_eclip_pmo', 'dilg_fms')
            && $this->hasVisibleRoleActivity($user, $case);
    }

    private function hasVisibleRoleActivity(User $user, EclipCase $case): bool
    {
        $role = match ($user->role) {
            'dilg_reviewer' => 'dilg_provincial_focal',
            'eclip_funding_officer' => 'dilg_fms',
            default => $user->role,
        };

        return $case->workflowActivities()
            ->where('status', '!=', 'locked')
            ->whereJsonContains('responsible_roles', $role)
            ->exists();
    }

    public function releaseAssistance(User $user, EclipCase $case): bool
    {
        return $user->hasRole('local_eclip_committee')
            && $user->municipality_id !== null
            && $user->municipality_id === $case->municipality_id
            && in_array($case->status, [EclipCaseStatus::FundsTransferred, EclipCaseStatus::ReleasePending], true);
    }

    public function downloadReleaseAcknowledgment(User $user, EclipCase $case): bool
    {
        return ($user->hasRole('local_eclip_committee') && $user->municipality_id === $case->municipality_id)
            || ($user->hasRole('lswdo') && $case->hasActiveParticipant($user, 'case_processor'));
    }

    public function manageFunding(User $user, EclipCase $case): bool
    {
        if ($user->hasRole('dilg_regional')) {
            return $case->status === EclipCaseStatus::FundsAllocated;
        }

        return ($user->hasRole('dilg_fms') || ($user->hasRole('eclip_funding_officer') && $user->municipality_id === $case->municipality_id))
            && in_array($case->status, [
                EclipCaseStatus::Approved,
                EclipCaseStatus::FundAllocationPending,
                EclipCaseStatus::FundsAllocated,
            ], true);
    }

    public function downloadFundingProof(User $user, EclipCase $case): bool
    {
        return $user->hasRole('dilg_fms', 'dilg_regional')
            || ($user->hasRole('eclip_funding_officer') && $user->municipality_id === $case->municipality_id);
    }

    public function assessAssistance(User $user, EclipCase $case): bool
    {
        return $user->hasRole('lswdo', 'eclip_assessor')
            && $case->hasActiveParticipant($user)
            && in_array($case->status, [
                EclipCaseStatus::DocumentsCertified,
                EclipCaseStatus::AssistanceAssessment,
                EclipCaseStatus::ReturnedForAssessmentRevision,
            ], true);
    }

    public function reviewForDilg(User $user, EclipCase $case): bool
    {
        return match ($user->role) {
            'dilg_provincial_focal' => $user->municipality_id === $case->municipality_id
                && $case->status === EclipCaseStatus::SubmittedForDilgReview,
            'dilg_regional' => $case->status === EclipCaseStatus::ProvincialEndorsed,
            'nboo_eclip_pmo' => $case->status === EclipCaseStatus::RegionalEndorsed,
            'dilg_reviewer' => $user->municipality_id === $case->municipality_id
                && $case->status === EclipCaseStatus::SubmittedForDilgReview,
            default => false,
        };
    }

    public function uploadDocument(User $user, EclipCase $case): bool
    {
        return $user->hasRole('lswdo') && $case->hasActiveParticipant($user) && in_array($case->status, [
            EclipCaseStatus::Eligible,
            EclipCaseStatus::DocumentProcessing,
            EclipCaseStatus::DocumentsIncomplete,
        ], true);
    }

    public function reviewDocument(User $user, EclipCase $case): bool
    {
        return $user->hasRole('japic')
            && $case->hasActiveParticipant($user, 'authentication_reviewer')
            && in_array($case->status, [
                EclipCaseStatus::DocumentProcessing,
                EclipCaseStatus::DocumentsIncomplete,
            ], true);
    }

    public function downloadDocument(User $user, EclipCase $case): bool
    {
        return $user->hasRole('lswdo', 'japic') && $case->hasActiveParticipant($user);
    }

    public function manageFea(User $user, EclipCase $case): bool
    {
        return $user->hasRole('pnp', 'afp')
            && $case->hasActiveParticipant($user, 'fea_processor')
            && $case->workflowActivities()->where('step_code', '4B')->exists();
    }

    public function uploadFeaDocument(User $user, EclipCase $case): bool
    {
        return $this->manageFea($user, $case)
            && $case->workflowActivities()
                ->where('step_code', '4B')
                ->whereIn('status', ['pending', 'ongoing', 'late', 'returned_for_correction'])
                ->exists();
    }

    public function assignFeaProcessor(User $user, EclipCase $case): bool
    {
        return $user->hasRole('lswdo')
            && $case->hasActiveParticipant($user, 'case_processor')
            && $case->workflowActivities()->where('step_code', '4B')->whereIn('status', ['pending', 'ongoing', 'late', 'returned_for_correction'])->exists();
    }

    public function viewFeaDocuments(User $user, EclipCase $case): bool
    {
        return match ($user->role) {
            'pnp', 'afp' => $case->hasActiveParticipant($user, 'fea_processor'),
            'lswdo' => $case->hasActiveParticipant($user, 'case_processor'),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function submit(User $user, EclipCase $case): bool
    {
        return $user->hasRole('mblrc')
            && $case->created_by === $user->id
            && $case->participantAssignments()
                ->where('is_active', true)
                ->whereHas('user', fn ($query) => $query->where('role', 'lswdo'))
                ->exists()
            && in_array($case->status, [
                EclipCaseStatus::Draft,
                EclipCaseStatus::ReturnedForCorrection,
            ], true);
    }

    public function reviewEligibility(User $user, EclipCase $case): bool
    {
        return $this->view($user, $case)
            && $user->hasRole('lswdo')
            && in_array($case->status, [
                EclipCaseStatus::SubmittedForEligibility,
                EclipCaseStatus::EligibilityReviewInProgress,
            ], true);
    }
}
