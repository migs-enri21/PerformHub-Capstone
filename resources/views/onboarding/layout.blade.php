@extends('layouts.guest')

@section('title', $title ?? 'Complete Sign Up')

@section('content')
<div class="onboarding-page py-4 py-lg-5">
    <div class="container" style="max-width: 640px;">
        @if(($current ?? 1) > 1)
            <a href="{{ route('onboarding.profile') }}" class="text-muted small mb-4 d-inline-block">
                <i class="fas fa-chevron-left me-1"></i> Back to Profile
            </a>
        @endif

        <div class="text-center mb-4">
            <span class="text-dark fw-bold fs-5 d-inline-flex align-items-center">
                <img src="{{ asset('images/logo.png') }}" alt="PerformHub" height="36" width="36" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub
            </span>
        </div>

        @include('onboarding.partials.stepper', [
            'current' => $current ?? 1,
            'steps' => auth()->user()?->isPerformer()
                ? [1 => 'Profile', 2 => 'Done']
                : [1 => 'Profile', 2 => 'Verification', 3 => 'Done'],
        ])

        @yield('onboarding-content')
    </div>
</div>
@endsection
