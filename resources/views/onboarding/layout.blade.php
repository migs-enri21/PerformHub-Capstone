@extends('layouts.guest')

@section('title', $title ?? 'Complete Sign Up')

@section('content')
<div class="onboarding-page py-4 py-lg-5">
    <div class="container" style="max-width: 640px;">
        <form method="POST" action="{{ route('logout') }}" class="mb-4">
            @csrf
            <button type="submit" class="btn btn-link text-muted small p-0 text-decoration-none">
                <i class="fas fa-chevron-left me-1"></i> Back to Home
            </button>
        </form>

        @include('onboarding.partials.stepper', ['current' => $current ?? 1])

        @yield('onboarding-content')

        <p class="text-center text-muted small mt-4 mb-0">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </p>
    </div>
</div>
@endsection
