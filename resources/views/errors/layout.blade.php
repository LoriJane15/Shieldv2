{{-- Branded error page, ported from the legacy 404.php. --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - SHIELD Program</title>
    <link rel="icon" href="{{ asset('assets/img/SHEILD.png') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .error-bg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: clamp(200px, 25vw, 400px);
            font-weight: 900;
            color: rgba(148, 163, 184, .13);
            user-select: none;
            z-index: 1;
            line-height: 1;
        }

        .error-container {
            text-align: center;
            position: relative;
            z-index: 2;
            max-width: 600px;
            padding: 2rem;
        }

        .error-title {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .error-subtitle {
            font-size: 1.125rem;
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .error-url {
            font-size: .875rem;
            color: #94a3b8;
            margin-bottom: 1.5rem;
            word-break: break-all;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: .875rem 1.5rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            transition: all .3s ease;
            cursor: pointer;
            border: none;
            display: inline-block;
        }

        .btn-primary { background: #2c4199; color: #fff; }
        .btn-primary:hover { background: #35127d; }
        .btn-secondary { background: #fff; color: #64748b; border: 2px solid #e2e8f0; }
        .btn-secondary:hover { border-color: #cbd5e1; }

        .footer-text { font-size: .8125rem; color: #94a3b8; margin-top: 2.5rem; }
        .shield-logo { width: 200px; margin: 1rem auto 0; opacity: .9; }
        .shield-logo img { width: 100%; height: auto; object-fit: contain; }
    </style>
</head>

<body>
    <div class="error-bg">@yield('code')</div>
    <div class="error-container">
        <h1 class="error-title">@yield('title')</h1>
        <p class="error-subtitle">@yield('message')</p>

        <p class="error-url">Requested URL: {{ request()->getRequestUri() }}</p>

        <div class="action-buttons">
            <button class="btn btn-secondary" onclick="goBack()">&larr; Go back</button>
            <a href="{{ url('/') }}" class="btn btn-primary">Take me home</a>
        </div>

        <p class="footer-text">
            SHIELD Program — Strengthening Institutions and Empowering Localities against Discrimination
        </p>

        <div class="shield-logo">
            <img src="{{ asset('assets/img/SHIELD horizontal.png') }}" alt="SHIELD Program">
        </div>
    </div>

    <script>
        function goBack() {
            if (window.history.length > 1) window.history.back();
            else window.location.href = @json(url('/'));
        }
    </script>
</body>

</html>
