@extends('layouts.skydash-v')
@section('title', 'Profile')
@section('heading', 'Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card grid-margin">
            <div class="card-body">
                <h4 class="card-title">Account</h4>
                <div class="row">
                    <div class="col-sm-6 mb-3">
                        <label class="form-label text-muted">Full name</label>
                        <p class="mb-0 fw-medium">{{ auth()->user()->name }}</p>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="form-label text-muted">Username</label>
                        <p class="mb-0 fw-medium">{{ '@'.auth()->user()->username }}</p>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="form-label text-muted">Role</label>
                        <p class="mb-0 fw-medium">{{ config('shield.roles.'.auth()->user()->role.'.label', auth()->user()->role) }}</p>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="form-label text-muted">Scope</label>
                        <p class="mb-0 fw-medium">{{ auth()->user()->municipality?->name ?? auth()->user()->govAgency?->acronym ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card grid-margin">
            <div class="card-body">
                <h4 class="card-title">Change Password</h4>
                @if (session('status') === 'password-updated')
                    <div class="alert alert-success">Password updated.</div>
                @endif
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Current password</label>
                        <div class="input-group">
                            <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-password-toggle title="Show password" aria-label="Show password">
                                <i class="ti-eye password-toggle-icon"></i>
                            </button>
                        </div>
                        @error('current_password', 'updatePassword') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New password</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-password-toggle title="Show password" aria-label="Show password">
                                <i class="ti-eye password-toggle-icon"></i>
                            </button>
                        </div>
                        @error('password', 'updatePassword') <p class="mt-1 text-danger small">{{ $message }}</p> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm new password</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary password-toggle-btn" data-password-toggle title="Show password" aria-label="Show password">
                                <i class="ti-eye password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-primary">Update password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .password-toggle-btn {
        min-width: 44px;
    }

    .password-toggle-icon {
        position: relative;
        display: inline-block;
    }

    .password-toggle-icon.is-hidden::after {
        content: "";
        position: absolute;
        left: -3px;
        top: 50%;
        width: 22px;
        height: 2px;
        background: currentColor;
        transform: rotate(-35deg);
        transform-origin: center;
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.closest('.input-group').querySelector('input');
            const icon = button.querySelector('i');
            const shouldShow = input.type === 'password';

            input.type = shouldShow ? 'text' : 'password';
            button.title = shouldShow ? 'Hide password' : 'Show password';
            button.setAttribute('aria-label', button.title);
            icon.classList.toggle('is-hidden', shouldShow);
        });
    });
</script>
@endpush
