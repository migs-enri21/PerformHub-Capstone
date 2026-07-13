@extends('layouts.guest')

@section('title', $title ?? 'Complete Sign Up')

@section('content')
<div class="onboarding-page py-4 py-lg-5">
    <div class="container" style="max-width: 640px;">
        <a href="{{ route('home') }}" class="text-muted small mb-4 d-inline-block">
            <i class="fas fa-chevron-left me-1"></i> Back to Home
        </a>

        <div class="text-center mb-4">
            <a href="{{ route('home') }}" class="text-white text-decoration-none fw-bold fs-5 d-inline-flex align-items-center">
                <img src="{{ asset('images/logo.png') }}" alt="PerformHub" height="36" width="36" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub
            </a>
        </div>

        @include('onboarding.partials.stepper', ['current' => $current ?? 1])

        @yield('onboarding-content')

        <p class="text-center text-muted small mt-4 mb-0">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </p>
    </div>
</div>
@endsection
