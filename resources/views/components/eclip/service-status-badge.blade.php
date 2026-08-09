@props(['status', 'overdue' => false])

@php
    $labels = [
        'pending' => 'Pending',
        'referred' => 'Referred',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'not_applicable' => 'Not Applicable',
    ];
    $displayStatus = $overdue ? 'overdue' : $status;
@endphp

<span {{ $attributes->class(['service-status', 'service-status-'.$displayStatus]) }}>
    <i class="mdi {{ $overdue ? 'mdi-clock-alert-outline' : ($status === 'completed' ? 'mdi-check-circle-outline' : ($status === 'not_applicable' ? 'mdi-minus-circle-outline' : 'mdi-circle-medium')) }}" aria-hidden="true"></i>
    {{ $overdue ? 'Overdue' : ($labels[$status] ?? str($status)->replace('_', ' ')->title()) }}
</span>
