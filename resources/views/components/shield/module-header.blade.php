@props([
    'eyebrow',
    'title',
    'description',
    'icon' => 'mdi-view-dashboard-outline',
    'role' => null,
])

<header {{ $attributes->class('shield-module-header') }}>
    <div class="shield-module-title">
        <span class="shield-module-icon"><i class="mdi {{ $icon }}" aria-hidden="true"></i></span>
        <div class="shield-module-copy">
            <div class="shield-module-eyebrow">{{ $eyebrow }}</div>
            <h2>{{ $title }}</h2>
            <p>{{ $description }}</p>
        </div>
    </div>
    <div class="shield-module-aside">
        @isset($actions)
            {{ $actions }}
        @else
            <span class="shield-module-role">{{ $role ?: str(auth()->user()->role)->upper() }}</span>
        @endisset
    </div>
</header>
