@props(['action'])
@php
    $normalized = str($action)->lower()->toString();
    $tone = match (true) {
        str_contains($normalized, 'fail'), str_contains($normalized, 'reject'), str_contains($normalized, 'denied'), str_contains($normalized, 'delete') => 'danger',
        str_contains($normalized, 'approve'), str_contains($normalized, 'complete'), str_contains($normalized, 'accept') => 'success',
        str_contains($normalized, 'warn'), str_contains($normalized, 'return') => 'warning',
        str_contains($normalized, 'update'), str_contains($normalized, 'replace') => 'purple',
        str_contains($normalized, 'login'), str_contains($normalized, 'create'), str_contains($normalized, 'upload'), str_contains($normalized, 'submit') => 'info',
        default => 'neutral',
    };
@endphp
<span {{ $attributes->class(['audit-action-badge', "audit-action-{$tone}"]) }}>{{ str($action)->replace('_', ' ')->upper() }}</span>
