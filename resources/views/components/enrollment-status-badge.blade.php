@props(['enrollment'])
@php
    $attention = $enrollment->needsAttention();
    $label = $attention ? 'Needs Attention' : str($enrollment->status)->replace('_', ' ')->title();
    $tone = $attention ? 'attention' : match ($enrollment->status) {
        'completed' => 'completed',
        default => 'active',
    };
@endphp
<span {{ $attributes->class(['enrollment-status-badge', "enrollment-status-{$tone}"]) }}>{{ $label }}</span>
