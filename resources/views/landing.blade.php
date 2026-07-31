<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SHIELD — Former Rebel Reintegration Platform</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-white">
    <header class="border-b border-slate-100">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
            <div class="flex items-center gap-2">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-peace-500 font-extrabold text-white">S</span>
                <span class="text-lg font-bold text-shield-900">SHIELD</span>
            </div>
            <nav class="flex items-center gap-6 text-sm font-medium text-slate-600">
                <a href="#about" class="hidden hover:text-shield-700 sm:block">About</a>
                <a href="#features" class="hidden hover:text-shield-700 sm:block">Features</a>
                @auth
                    <a href="{{ route(auth()->user()->homeRoute()) }}" class="btn-primary">Go to workspace</a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <section class="relative overflow-hidden">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-6 py-20 lg:grid-cols-2">
            <div data-reveal>
                <span class="badge-green">Davao del Sur · Peace &amp; Development</span>
                <h1 class="mt-4 text-4xl font-extrabold leading-tight text-shield-950 sm:text-5xl">
                    Reintegrating Former Rebels, together.
                </h1>
                <p class="mt-4 max-w-xl text-lg text-slate-600">
                    SHIELD unifies registration, RCSP barangay monitoring, and inter-agency
                    implementation planning into one platform for the Katuparan Center and its partners.
                </p>
                <div class="mt-8 flex gap-3">
                    <a href="{{ route('login') }}" class="btn-primary">Access the platform</a>
                    <a href="#features" class="btn-ghost">Learn more</a>
                </div>
            </div>
            <div data-reveal class="relative">
                <div class="rounded-2xl bg-gradient-to-br from-shield-800 to-shield-600 p-8 text-white shadow-xl">
                    <div class="grid grid-cols-2 gap-4">
                        @foreach ([
                            ['Former Rebels', \App\Models\FormerRebel::count()],
                            ['RCSP Barangays', \App\Models\RcspBarangay::count()],
                            ['Partner Agencies', \App\Models\GovAgency::count()],
                            ['Municipalities', \App\Models\Municipality::count()],
                        ] as [$label, $value])
                            <div class="rounded-xl bg-white/10 p-5 backdrop-blur">
                                <p class="text-3xl font-bold">{{ $value }}</p>
                                <p class="mt-1 text-sm text-shield-100">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="border-t border-slate-100 bg-slate-50 py-20">
        <div class="mx-auto max-w-6xl px-6">
            <h2 class="text-2xl font-bold text-shield-900">One platform, seven stakeholders</h2>
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Former Rebel Registry', 'Register, profile, geolocate and monitor reintegration progress with skills and assistance tracking.'],
                    ['RCSP Monitoring', 'Six-phase barangay monitoring with per-activity file uploads and review threads.'],
                    ['IMPLAN Lifecycle', 'Create implementation plans, tag agencies, accept/reject, verify and reassign.'],
                    ['Interactive Maps', 'Leaflet-powered infestation maps and FR location clustering.'],
                    ['Inter-agency Workflow', 'DILG, DAR, DA, TESDA and more coordinated in one place.'],
                    ['Role-based Access', 'Tailored workspaces for every partner from Super Admin to AFP.'],
                ] as [$t, $d])
                    <div data-reveal class="card p-6">
                        <h3 class="font-semibold text-shield-800">{{ $t }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="bg-shield-950 py-8 text-center text-sm text-shield-300">
        &copy; {{ now(config('app.display_timezone'))->year }} SHIELD Program · Katuparan Center. All rights reserved.
    </footer>
</body>
</html>
