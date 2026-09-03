<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $shieldWorkspaceRoles = ['mblrc', 'lswdo', 'japic', 'pnp', 'local_eclip_committee', '39th_ib'];
        $isShieldWorkspace = auth()->check() && in_array(auth()->user()->role, $shieldWorkspaceRoles, true);
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SHIELD</title>

    <link rel="stylesheet" href="{{ asset('assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dropdown-indicators.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/SHEILD.png') }}">
    @stack('styles')
    @if ($isShieldWorkspace)
        <link rel="stylesheet" href="{{ asset('assets/css/mblrc-workspace.css') }}">
    @endif
</head>
<body class="sidebar-fixed {{ $isShieldWorkspace ? 'mblrc-interface shield-role-interface shield-role-'.auth()->user()->role : '' }}">
@php
    $user = auth()->user();
    $role = $user->role;
    $meta = config("shield.roles.$role");
    $nav = collect($meta['nav'] ?? [])->filter(fn ($i) => \Illuminate\Support\Facades\Route::has($i['route']));
@endphp
<div class="container-scroller">
    {{-- Top navbar --}}
    <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
            <a class="navbar-brand brand-logo" href="{{ route(auth()->user()->homeRoute()) }}">
                <img src="{{ asset('assets/img/SHIELDlogo.png') }}" alt="SHIELD Index System">
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route(auth()->user()->homeRoute()) }}">
                <img src="{{ asset('assets/img/SHEILD.png') }}" alt="logo" />
            </a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-stretch">
            @if ($isShieldWorkspace)
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize" aria-label="Toggle sidebar navigation">
                    <span class="icon-menu"></span>
                </button>
                @if ($role === 'mblrc')
                    <form class="mblrc-navbar-search d-none d-md-flex" method="GET" action="{{ route('mblrc.fr.index') }}" role="search">
                        <i class="mdi mdi-magnify" aria-hidden="true"></i>
                        <label class="sr-only" for="mblrc-navbar-search">Search the FR/FVE registry</label>
                        <input id="mblrc-navbar-search" name="search" type="search" maxlength="100" placeholder="Search registry…" autocomplete="off">
                        <button type="submit">Search</button>
                    </form>
                @elseif ($role === '39th_ib')
                    <form class="mblrc-navbar-search d-none d-md-flex" method="GET" action="{{ route('ib39.fr-profiles.index') }}" role="search">
                        <i class="mdi mdi-magnify" aria-hidden="true"></i>
                        <label class="sr-only" for="ib39-navbar-search">Search FR profiles</label>
                        <input id="ib39-navbar-search" name="search" type="search" maxlength="100" placeholder="Search reference, first or last name…" autocomplete="off">
                        <button type="submit">Search</button>
                    </form>
                @endif
            @else
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="icon-menu"></span>
                </button>
                <span class="ms-3 align-self-center h5 mb-0 text-dark d-none d-md-block">@yield('heading', $meta['label'] ?? '')</span>
            @endif
            <ul class="navbar-nav navbar-nav-right ms-auto">
                <li class="nav-item d-flex align-items-center mr-2">
                    @php
                        $unreadNotificationCount = $user->unreadNotifications()->count();
                    @endphp
                    <a class="nav-link position-relative" href="{{ route('notifications.index') }}" aria-label="Notifications" data-notification-link data-notification-status-url="{{ route('notifications.status') }}">
                        <i class="ti-bell"></i>
                        <span class="badge badge-danger {{ $unreadNotificationCount ? '' : 'd-none' }}" data-notification-badge>{{ min($unreadNotificationCount, 99) }}</span>
                    </a>
                </li>
                <li class="nav-item nav-profile dropdown">
                    <a class="nav-link dropdown-toggle nav-profile-trigger d-flex align-items-center" href="#" data-bs-toggle="dropdown" id="profileDropdown">
                        <div class="nav-profile-avatar">
                            <img src="{{ $user->logo_url }}"
                                 onerror="this.onerror=null;this.src='{{ asset('assets/img/kc-logo.svg') }}'"
                                 alt="{{ $user->name }} logo" />
                        </div>
                        <span class="nav-profile-name text-truncate" title="{{ $user->name }}">
                            {{ $user->name }}
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                        <h6 class="dropdown-header mb-0 text-truncate">{{ auth()->user()->name }}</h6>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="ti-settings text-primary"></i> Settings</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="dropdown-item" type="button" data-logout-open onclick="return openLogoutModal(event);"><i class="ti-power-off text-primary"></i> Logout</button>
                        </form>
                    </div>
                </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                <span class="icon-menu"></span>
            </button>
        </div>
    </nav>

    <div class="container-fluid page-body-wrapper">
        {{-- Sidebar --}}
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">
                @php
                    $currentSection = null;
                @endphp
                @foreach ($nav as $item)
                    @php
                        $itemSection = $item['section'] ?? null;
                        $patterns = [$item['route']];
                        // match sibling child routes (e.g. lgu.rcsp.* for lgu.rcsp.index) — only for resource routes, not role.dashboard
                        if (substr_count($item['route'], '.') >= 2) {
                            $patterns[] = preg_replace('/[^.]+$/', '*', $item['route']);
                        }
                        $patterns = array_merge($patterns, (array) ($item['active'] ?? []));
                        $isActive = request()->routeIs(...$patterns);
                    @endphp
                    @if ($isShieldWorkspace && $itemSection !== $currentSection)
                        @php
                            $currentSection = $itemSection;
                        @endphp
                        @if ($currentSection)
                            <li class="mblrc-nav-section" aria-hidden="true">{{ $currentSection }}</li>
                        @endif
                    @endif
                    <li class="nav-item {{ $isActive ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route($item['route']) }}">
                            <i class="{{ $item['skyicon'] ?? 'icon-grid' }} menu-icon"></i>
                            <span class="menu-title">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="main-panel">
            <div class="content-wrapper">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('assets/js/jquery.cookie.js') }}"></script>
<script src="{{ asset('assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>
<script src="{{ asset('assets/js/settings.js') }}"></script>
<script src="{{ asset('assets/vendors/chart.js/Chart.min.js') }}"></script>
@include('layouts.partials.logout-confirmation')
@include('layouts.partials.notification-realtime')
@stack('scripts')
</body>
</html>
