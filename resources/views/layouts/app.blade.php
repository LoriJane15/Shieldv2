<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SHIELD</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans">
@php
    $role = auth()->user()->role;
    $meta = config("shield.roles.$role");
    $nav = collect($meta['nav'] ?? [])->filter(fn ($i) => \Illuminate\Support\Facades\Route::has($i['route']));
@endphp

<div class="min-h-screen lg:flex">
    {{-- Sidebar --}}
    <aside data-sidebar
           class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform bg-shield-900 transition-transform duration-200 lg:static lg:translate-x-0">
        <div class="flex h-16 items-center gap-2 px-5 text-white">
            <span class="grid h-9 w-9 place-items-center rounded-lg bg-peace-500 font-extrabold">S</span>
            <div class="leading-tight">
                <p class="text-sm font-bold tracking-wide">SHIELD</p>
                <p class="text-[11px] text-shield-200">{{ $meta['label'] ?? '' }}</p>
            </div>
        </div>
        <nav class="space-y-1 px-3 py-4">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="nav-link {{ request()->routeIs($item['route']) ? 'nav-link-active' : '' }}">
                    <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="h-5 w-5 shrink-0" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>
    <div data-sidebar-backdrop class="fixed inset-0 z-30 hidden bg-slate-900/40 lg:hidden"></div>

    {{-- Main column --}}
    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Topbar --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
            <button data-sidebar-open class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                <x-heroicon-o-bars-3 class="h-6 w-6" />
            </button>
            <h1 class="text-lg font-semibold text-slate-800">@yield('heading', 'Dashboard')</h1>

            <div class="relative ml-auto flex items-center gap-3" data-dropdown>
                <button data-dropdown-trigger class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-slate-100">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-shield-700 text-sm font-semibold text-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ auth()->user()->name }}</span>
                    <x-heroicon-o-chevron-down class="h-4 w-4 text-slate-400" />
                </button>
                <div data-dropdown-menu
                     class="absolute right-0 top-12 hidden w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="button" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-slate-50" data-logout-open onclick="return openLogoutModal(event);">Log out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>
    </div>
</div>

@include('layouts.partials.logout-confirmation')

{{-- Flash toasts --}}
<div class="pointer-events-none fixed bottom-4 right-4 z-50 space-y-2">
    @foreach (['success' => 'peace', 'error' => 'red', 'status' => 'shield'] as $key => $tone)
        @if (session($key))
            <div data-toast class="pointer-events-auto flex items-center gap-2 rounded-lg bg-white px-4 py-3 shadow-lg ring-1 ring-slate-200">
                <span class="h-2 w-2 rounded-full bg-{{ $tone }}-500"></span>
                <span class="text-sm text-slate-700">{{ session($key) }}</span>
            </div>
        @endif
    @endforeach
</div>
</body>
</html>
