@props(['status'])

@php
    $tone = match ($status) {
        'completed', 'not_applicable' => 'complete',
        'ongoing' => 'active',
        'pending' => 'pending',
        'late', 'returned_for_correction' => 'attention',
        'not_eligible' => 'rejected',
        default => 'locked',
    };
    $label = match ($status) {
        'pending' => 'Not Started',
        'ongoing' => 'In Progress',
        'returned_for_correction' => 'Returned',
        'late' => 'Overdue',
        'not_applicable' => 'Not Applicable',
        default => str($status)->replace('_', ' ')->title()->toString(),
    };
@endphp

<span {{ $attributes->class(['workflow-status-badge', 'workflow-status-'.$tone]) }}>{{ $label }}</span>
