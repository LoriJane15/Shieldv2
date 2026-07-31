<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login — SHIELD</title>
    <link rel="stylesheet" href="{{ asset('assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vertical-layout-light/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/SHEILD.png') }}" />
    <style>
        .password-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 100%;
            padding: 0;
            border: 1px solid #ced4da;
            border-left: 0;
            border-radius: 0 4px 4px 0;
            background: #fff;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        .password-toggle:hover,
        .password-toggle:focus {
            background: #f8f9fa;
            color: #35127d;
            outline: none;
        }

        .password-toggle:focus-visible {
            box-shadow: 0 0 0 3px rgba(53, 18, 125, 0.2);
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }
    </style>
</head>

<body class="sidebar-icon-only">
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
                <div class="row flex-grow">
                    <div class="col-lg-6 d-flex align-items-center justify-content-center">
                        <div class="auth-form-transparent text-left p-3">
                            <div class="mb-3">
                                <a href="{{ route('landing') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti-arrow-left mr-1"></i> Back
                                </a>
                            </div>
                            <div class="brand-logo">
                                <img src="{{ asset('assets/img/SHIELD horizontal.png') }}" alt="logo">
                            </div>
                            <h2 class="font-weight-bold">Login<span class="text-warning">.</span></h2>
                            <h5 class="font-weight-light">Happy to see you again!</h5>

                            @if (session('status'))
                                <div class="alert alert-info">{{ session('status') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">{{ $errors->first() }}</div>
                            @endif

                            <form class="pt-3" method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend bg-transparent">
                                            <span class="input-group-text bg-transparent border-right-0">
                                                <i class="ti-user text-primary"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control form-control-lg border-left-0"
                                               id="username" name="username" value="{{ old('username') }}"
                                               placeholder="username" required autofocus autocomplete="username">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend bg-transparent">
                                            <span class="input-group-text bg-transparent border-right-0">
                                                <i class="ti-lock text-primary"></i>
                                            </span>
                                        </div>
                                        <input type="password" class="form-control form-control-lg border-left-0 border-right-0"
                                               id="password" name="password" placeholder="password"
                                               required autocomplete="current-password">
                                        <div class="input-group-append">
                                            <button type="button" class="password-toggle" id="passwordToggle"
                                                    aria-label="Show password" aria-controls="password"
                                                    aria-pressed="false" title="Show password">
                                                <svg data-password-show aria-hidden="true" viewBox="0 0 24 24"
                                                     fill="none" stroke="currentColor" stroke-width="2"
                                                     stroke-linecap="round" stroke-linejoin="round" hidden>
                                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                                <svg data-password-hide aria-hidden="true" viewBox="0 0 24 24"
                                                     fill="none" stroke="currentColor" stroke-width="2"
                                                     stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 3l18 18"></path>
                                                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                                    <path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c6.5 0 10 8 10 8a18 18 0 0 1-2.2 3.3"></path>
                                                    <path d="M6.6 6.6C3.6 8.5 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 5.4-1.6"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="my-2 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <label class="form-check-label text-muted">
                                            <input type="checkbox" class="form-check-input" name="remember"> Keep me signed in
                                        </label>
                                    </div>
                                    <span class="auth-link text-muted" title="Accounts are managed by the Super Admin">
                                        Forgot password? Contact your Super Admin.
                                    </span>
                                </div>
                                <div class="my-2">
                                    <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">LOGIN</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 login-half-bg d-flex flex-row">
                        <p class="text-white font-weight-medium text-center flex-grow align-self-end">
                            Copyright &copy; {{ now(config('app.display_timezone'))->year }} All rights reserved.
                        </p>
                    </div>
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
    <script>
        (() => {
            const password = document.getElementById('password');
            const toggle = document.getElementById('passwordToggle');

            if (!password || !toggle) {
                return;
            }

            toggle.addEventListener('click', () => {
                const isVisible = password.type === 'text';
                const label = isVisible ? 'Show password' : 'Hide password';

                password.type = isVisible ? 'password' : 'text';
                toggle.setAttribute('aria-label', label);
                toggle.setAttribute('aria-pressed', String(!isVisible));
                toggle.setAttribute('title', label);
                toggle.querySelector('[data-password-show]').hidden = isVisible;
                toggle.querySelector('[data-password-hide]').hidden = !isVisible;
                password.focus();
            });
        })();
    </script>
</body>

</html>
