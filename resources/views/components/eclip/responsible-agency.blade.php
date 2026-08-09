@props(['labels'])

<span {{ $attributes->class(['responsible-agency']) }}><i class="mdi mdi-office-building-outline" aria-hidden="true"></i><span>{{ implode(' / ', $labels) }}</span></span>
