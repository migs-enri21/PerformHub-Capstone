@extends('layouts.guest')

@section('title', $title ?? 'Complete Sign Up')

@section('content')
<div class="onboarding-page py-4 py-lg-5">
    <div class="container" style="max-width: 640px;">
        @if(($current ?? 1) < 3)
            <div class="d-flex justify-content-between align-items-center mb-4">
                <form method="POST" action="{{ route('onboarding.exit') }}">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted small p-0 text-decoration-none">
                        <i class="fas fa-house me-1"></i> Back to Home
                    </button>
                </form>
                @if(($current ?? 1) > 1)
                    <a href="{{ route('onboarding.profile') }}" class="text-muted small">
                        <i class="fas fa-chevron-left me-1"></i> Back to Profile
                    </a>
                @endif
            </div>
        @endif

        <div class="text-center mb-4">
            <span class="text-dark fw-bold fs-5 d-inline-flex align-items-center">
                <img src="{{ asset('images/logo.png') }}" alt="PerformHub" height="36" width="36" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub
            </span>
        </div>

        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('onboarding.partials.stepper', [
            'current' => $current ?? 1,
            'steps' => [1 => 'Profile', 2 => 'Verification', 3 => 'Done'],
        ])

        @yield('onboarding-content')
    </div>
</div>
@endsection
