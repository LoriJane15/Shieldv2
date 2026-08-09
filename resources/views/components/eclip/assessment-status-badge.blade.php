@props(['status'])

@php
    $tone = match ($status) {
        App\Enums\EclipCaseStatus::DocumentsCertified => 'ready',
        App\Enums\EclipCaseStatus::AssistanceAssessment => 'progress',
        App\Enums\EclipCaseStatus::SubmittedForDilgReview => 'review',
        App\Enums\EclipCaseStatus::ReturnedForAssessmentRevision => 'revision',
        App\Enums\EclipCaseStatus::Approved => 'approved',
        App\Enums\EclipCaseStatus::Rejected => 'rejected',
        default => 'neutral',
    };
@endphp

<span {{ $attributes->class(['assessment-status', 'assessment-status-'.$tone]) }}>
    <i class="mdi {{ match ($tone) { 'approved' => 'mdi-check-circle-outline', 'rejected' => 'mdi-close-circle-outline', 'revision' => 'mdi-alert-circle-outline', 'review' => 'mdi-clock-outline', 'ready' => 'mdi-file-check-outline', default => 'mdi-progress-clock' } }}" aria-hidden="true"></i>
    {{ $status->label() }}
</span>
