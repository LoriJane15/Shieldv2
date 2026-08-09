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
            return in_array($case->status, [
                EclipCaseStatus::DocumentProcessing,
                EclipCaseStatus::DocumentsIncomplete,
                EclipCaseStatus::DocumentsCertified,
            ], true);
        }

        if ($user->hasRole('lswdo', 'eclip_assessor')) {
            return $case->hasActiveParticipant($user)
                && in_array($case->status, [
                    EclipCaseStatus::SubmittedForEligibility,
                    EclipCaseStatus::EligibilityReviewInProgress,
                    EclipCaseStatus::ReturnedForCorrection,
                    EclipCaseStatus::Eligible,
                    EclipCaseStatus::Ineligible,
                    EclipCaseStatus::DocumentProcessing,
                    EclipCaseStatus::DocumentsIncomplete,
                    EclipCaseStatus::DocumentsCertified,
                    EclipCaseStatus::AssistanceAssessment,
                    EclipCaseStatus::SubmittedForDilgReview,
                    EclipCaseStatus::ProvincialEndorsed,
                    EclipCaseStatus::RegionalEndorsed,
                    EclipCaseStatus::ReturnedForAssessmentRevision,
                    EclipCaseStatus::Approved,
                    EclipCaseStatus::Rejected,
                ], true);
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
            return in_array($case->status, [
                EclipCaseStatus::ProvincialEndorsed,
                EclipCaseStatus::RegionalEndorsed,
                EclipCaseStatus::Approved,
                EclipCaseStatus::Rejected,
                EclipCaseStatus::ReturnedForAssessmentRevision,
            ], true);
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

    public function releaseAssistance(User $user, EclipCase $case): bool
    {
        return $user->hasRole('local_eclip_committee')
            && $user->municipality_id !== null
            && $user->municipality_id === $case->municipality_id
            && in_array($case->status, [EclipCaseStatus::FundsTransferred, EclipCaseStatus::ReleasePending], true);
    }

    public function downloadReleaseAcknowledgment(User $user, EclipCase $case): bool
    {
        return $user->hasRole('local_eclip_committee')
            && $user->municipality_id === $case->municipality_id;
    }

    public function manageFunding(User $user, EclipCase $case): bool
    {
        return ($user->hasRole('dilg_fms') || ($user->hasRole('eclip_funding_officer') && $user->municipality_id === $case->municipality_id))
            && in_array($case->status, [
                EclipCaseStatus::Approved,
                EclipCaseStatus::FundAllocationPending,
                EclipCaseStatus::FundsAllocated,
            ], true);
    }

    public function downloadFundingProof(User $user, EclipCase $case): bool
    {
        return $user->hasRole('dilg_fms')
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
        return $user->hasRole('japic') && in_array($case->status, [
            EclipCaseStatus::DocumentProcessing,
            EclipCaseStatus::DocumentsIncomplete,
        ], true);
    }

    public function downloadDocument(User $user, EclipCase $case): bool
    {
        return $user->hasRole('lswdo', 'japic') && $case->hasActiveParticipant($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function submit(User $user, EclipCase $case): bool
    {
        return false;
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
