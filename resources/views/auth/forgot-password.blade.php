@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-split">
    <div class="auth-hero d-none d-lg-block">
        <div class="p-4">
            <a href="{{ route('home') }}" class="text-white text-decoration-none fw-bold fs-5 d-flex align-items-center">
                <img src="{{ asset('images/logo.png') }}" alt="PerformHub" height="32" width="32" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub
            </a>
        </div>
        <div class="auth-hero-content">
            <h1 class="display-5 fw-bold">Get back on stage.</h1>
            <p class="text-white-50 fs-5">We will send a secure password reset link to your email.</p>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="w-100" style="max-width: 440px; margin: 0 auto;">
            <a href="{{ route('login') }}" class="text-muted small mb-4 d-inline-block">
                <i class="fas fa-chevron-left me-1"></i> Back to Sign In
            </a>
            <h2 class="fw-bold mb-1">Forgot your password?</h2>
            <p class="text-muted mb-4">Enter your email and we will send you a reset link.</p>

            @if(session('status'))<div class="alert alert-success py-2">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label text-muted small">Email Address</label>
                    <input type="email" name="email" class="form-control ph-input" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                </div>
                <button type="submit" class="btn ph-btn-primary w-100">Send Reset Link <i class="fas fa-arrow-right ms-2"></i></button>
            </form>
        </div>
    </div>
</div>
@endsection