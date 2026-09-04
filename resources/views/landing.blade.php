<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>SHIELD 2.0</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-5xl items-center px-6 py-12">
        <section class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="bg-gradient-to-br from-shield-950 to-shield-700 px-8 py-14 text-white sm:px-14">
                <p class="text-sm font-semibold uppercase tracking-widest text-shield-200">Katuparan Center · Department of the Interior and Local Government</p>
                <h1 class="mt-4 text-4xl font-extrabold sm:text-5xl">SHIELD 2.0</h1>
                <p class="mt-3 max-w-3xl text-lg font-semibold text-shield-100">Strengthening Institutions and Empowering Localities Against Discrimination 2.0</p>
                <p class="mt-6 max-w-2xl leading-7 text-shield-100">A secure, role-based government platform for authorized monitoring, records management, and inter-agency coordination.</p>
                <div class="mt-8">
                    @auth
                        <a href="{{ route(auth()->user()->homeRoute()) }}" class="inline-flex rounded-lg bg-white px-5 py-3 font-bold text-shield-800 shadow hover:bg-shield-50">Open assigned workspace</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex rounded-lg bg-white px-5 py-3 font-bold text-shield-800 shadow hover:bg-shield-50">Authorized user login</a>
                    @endauth
                </div>
            </div>
            <div class="grid gap-6 px-8 py-8 text-sm sm:grid-cols-2 sm:px-14">
                <div>
                    <h2 class="font-bold text-slate-900">Authorized-use notice</h2>
                    <p class="mt-2 leading-6 text-slate-600">Access is limited to accounts provisioned by the Super Administrator for official duties. Activity may be logged and reviewed. Unauthorized access or disclosure is prohibited.</p>
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">Privacy notice</h2>
                    <p class="mt-2 leading-6 text-slate-600">SHIELD 2.0 processes sensitive government and beneficiary information. Users must access only records explicitly assigned to their role and official responsibility.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
