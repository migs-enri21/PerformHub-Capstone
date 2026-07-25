@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="auth-split">
    <div class="auth-hero d-none d-lg-block">
        <div class="p-4">
            <a href="{{ route('home') }}" class="text-white text-decoration-none fw-bold fs-5 d-flex align-items-center">
                <img src="{{ asset('images/logo.png') }}" alt="PerformHub" height="32" width="32" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub
            </a>
        </div>
        <div class="auth-hero-content">
            <h1 class="display-5 fw-bold">Welcome back to the stage.</h1>
            <p class="text-white-50 fs-5">Thousands of performers and organizers are waiting for you.</p>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="w-100" style="max-width: 440px; margin: 0 auto;">
            <a href="{{ route('home') }}" class="text-muted small mb-4 d-inline-block">
                <i class="fas fa-chevron-left me-1"></i> Back to Home
            </a>

            <h2 class="fw-bold mb-1">Sign in to your account</h2>
            <p class="text-muted mb-4">Enter your credentials to continue</p>

            @if(session('warning'))
                <div class="alert alert-warning py-2">{{ session('warning') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label text-muted small">Email Address</label>
                    <input type="email" name="email" class="form-control ph-input" placeholder="you@example.com" value="{{ old('email') }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small">Password</label>
                    <input type="password" name="password" class="form-control ph-input" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn ph-btn-primary w-100 mb-3">
                    Sign In <i class="fas fa-arrow-right ms-2"></i>
                </button>

                <p class="text-center text-muted small mb-0">
                    Don't have an account? <a href="{{ route('register') }}">Create one</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection

