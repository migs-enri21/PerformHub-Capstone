@extends('layouts.app')

@section('title', 'Performer Dashboard')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
@include('partials.onboarding-banner')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Welcome, {{ $profile?->stage_name ?? auth()->user()->name }}</h2>
        <p class="text-muted mb-0">
            @if($profile?->is_verified_badge)
                <span class="verified-badge"><i class="fas fa-circle-check"></i> Verified Performer</span>
            @else
                @if(auth()->user()->hasLimitedAccess())
                    <span class="text-warning"><i class="fas fa-lock me-1"></i> Limited access — complete sign-up to get verified.</span>
                @else
                    Complete your profile to get verified.
                @endif
            @endif
        </p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="ph-card p-4 stat-card">
            <h3 class="fw-bold mb-0">{{ $pendingBookings }}</h3>
            <p class="text-muted mb-0 small">Pending Requests</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ph-card p-4 stat-card">
            <h3 class="fw-bold mb-0">{{ $upcomingBookings }}</h3>
            <p class="text-muted mb-0 small">Upcoming Bookings</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ph-card p-4 stat-card">
            <h3 class="fw-bold mb-0">{{ $reviews->count() }}</h3>
            <p class="text-muted mb-0 small">Recent Reviews</p>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-3">Quick Actions</h5>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('performer.profile.show') }}" class="btn ph-btn-outline btn-sm">View Profile</a>
                <a href="{{ route('performer.profile.edit') }}" class="btn ph-btn-outline btn-sm">Edit Profile</a>
                @if(auth()->user()->hasLimitedAccess())
                    <a href="{{ auth()->user()->onboardingRoute() }}" class="btn ph-btn-primary btn-sm">Complete Sign-up</a>
                @else
                    <a href="{{ route('performer.portfolio.index') }}" class="btn ph-btn-outline btn-sm">Upload Portfolio</a>
                    <a href="{{ route('performer.profile.show') }}#availability" class="btn ph-btn-outline btn-sm">Set Availability</a>
                    <a href="{{ route('performer.bookings.index') }}" class="btn ph-btn-primary btn-sm">View Bookings</a>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-3">Recent Reviews</h5>
            @forelse($reviews as $review)
                <div class="mb-3 pb-3 border-bottom" style="border-color: var(--ph-border) !important;">
                    <div class="text-warning small mb-1">@for($i=0;$i<$review->rating;$i++)<i class="fas fa-star"></i>@endfor</div>
                    <p class="small text-muted mb-1">{{ Str::limit($review->comment, 80) }}</p>
                    <small class="text-muted">— {{ $review->reviewer->name }}</small>
                </div>
            @empty
                <p class="text-muted small mb-0">No reviews yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
