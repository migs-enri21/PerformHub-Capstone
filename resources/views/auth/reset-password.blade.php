@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
<div class="auth-split">
    <div class="auth-hero d-none d-lg-block">
        <div class="p-4">
            <a href="{{ route('home') }}" class="text-white text-decoration-none fw-bold fs-5 d-flex align-items-center">
                <img src="{{ asset('images/logo.png') }}" alt="PerformHub" height="32" width="32" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub
            </a>
        </div>
        <div class="auth-hero-content">
            <h1 class="display-5 fw-bold">Choose a new password.</h1>
            <p class="text-white-50 fs-5">Use at least 8 characters to keep your account secure.</p>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="w-100" style="max-width: 440px; margin: 0 auto;">
            <a href="{{ route('login') }}" class="text-muted small mb-4 d-inline-block">
                <i class="fas fa-chevron-left me-1"></i> Back to Sign In
            </a>
            <h2 class="fw-bold mb-1">Reset your password</h2>
            <p class="text-muted mb-4">Enter your email and choose a new password.</p>

            @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="mb-3">
                    <label class="form-label text-muted small">Email Address</label>
                    <input type="email" name="email" class="form-control ph-input" value="{{ old('email', $email) }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">New Password</label>
                    <input type="password" name="password" class="form-control ph-input" required autocomplete="new-password">
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control ph-input" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn ph-btn-primary w-100">Reset Password <i class="fas fa-arrow-right ms-2"></i></button>
            </form>
        </div>
    </div>
</div>
@endsection