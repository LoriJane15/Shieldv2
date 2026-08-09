@props(['status'])

@php
    $tone = match ($status) {
        \App\Enums\EclipCaseStatus::SubmittedForEligibility,
        \App\Enums\EclipCaseStatus::EligibilityReviewInProgress => 'submitted',
        \App\Enums\EclipCaseStatus::Eligible => 'eligible',
        \App\Enums\EclipCaseStatus::DocumentsCertified => 'certified',
        \App\Enums\EclipCaseStatus::DocumentsIncomplete,
        \App\Enums\EclipCaseStatus::ReturnedForCorrection,
        \App\Enums\EclipCaseStatus::ReturnedForAssessmentRevision => 'revision',
        \App\Enums\EclipCaseStatus::Ineligible,
        \App\Enums\EclipCaseStatus::Rejected => 'ineligible',
        default => 'neutral',
    };
@endphp

<span {{ $attributes->class(['eclip-status', 'eclip-status-'.$tone]) }}>{{ $status->label() }}</span>
