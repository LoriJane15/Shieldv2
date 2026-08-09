@props(['case'])

@php
    $canAssess = auth()->user()?->can('assessAssistance', $case) ?? false;
    $label = match ($case->status) {
        App\Enums\EclipCaseStatus::DocumentsCertified => 'Start Assessment',
        App\Enums\EclipCaseStatus::AssistanceAssessment => 'Continue Assessment',
        App\Enums\EclipCaseStatus::ReturnedForAssessmentRevision => 'Review Revision',
        default => 'View Assessment',
    };
@endphp

<a href="{{ route('eclip_assessor.cases.show', $case) }}"
   {{ $attributes->class(['btn', 'assessment-action', 'btn-primary' => $canAssess, 'btn-outline-primary' => ! $canAssess]) }}
   aria-label="{{ $label }} for case {{ $case->case_number }}">
    <span>{{ $label }}</span><i class="mdi mdi-arrow-right" aria-hidden="true"></i>
</a>
