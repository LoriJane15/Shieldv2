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
@endphp

<span {{ $attributes->class(['workflow-status-badge', 'workflow-status-'.$tone]) }}>{{ str($status)->replace('_', ' ')->title() }}</span>
