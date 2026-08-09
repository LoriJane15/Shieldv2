<?php

namespace App\Enums;

enum EclipCaseStatus: string
{
    case Draft = 'draft';
    case SubmittedForEligibility = 'submitted_for_eligibility';
    case EligibilityReviewInProgress = 'eligibility_review_in_progress';
    case ReturnedForCorrection = 'returned_for_correction';
    case Eligible = 'eligible';
    case AuthenticationPending = 'authentication_pending';
    case AuthenticationUnderReview = 'authentication_under_review';
    case AuthenticationReturned = 'authentication_returned';
    case Authenticated = 'authenticated';
    case NotAuthenticated = 'not_authenticated';
    case Ineligible = 'ineligible';
    case PreviouslyAssisted = 'previously_assisted';
    case ForClarification = 'for_clarification';
    case DocumentProcessing = 'document_processing';
    case DocumentsIncomplete = 'documents_incomplete';
    case DocumentsCertified = 'documents_certified';
    case AssistanceAssessment = 'assistance_assessment';
    case SubmittedForDilgReview = 'submitted_for_dilg_review';
    case ProvincialEndorsed = 'provincial_endorsed';
    case RegionalEndorsed = 'regional_endorsed';
    case ReturnedForAssessmentRevision = 'returned_for_assessment_revision';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case FundAllocationPending = 'fund_allocation_pending';
    case FundsAllocated = 'funds_allocated';
    case FundsTransferred = 'funds_transferred';
    case ReleasePending = 'release_pending';
    case AssistanceReleased = 'assistance_released';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        if ($this === self::SubmittedForDilgReview) {
            return 'Submitted for Provincial Review';
        }

        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
