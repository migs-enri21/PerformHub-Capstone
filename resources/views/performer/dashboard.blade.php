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
<div class="event-feed-section mt-4">
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1">Available Events</h4>
        <p class="text-muted small mb-0">Open gigs posted by organizers</p>
    </div>

    @if($availableEvents->isNotEmpty())
        <div class="event-feed-center">
            @foreach($availableEvents as $event)
                @include('partials.event-feed-post', [
                    'event' => $event,
                    'hasApplied' => in_array($event->id, $appliedEventIds ?? [], true),
                ])
            @endforeach
        </div>
    @else
        <div class="event-feed-center">
            <div class="event-feed-empty text-muted text-center">
                No events available right now. Check back when organizers post open gigs.
            </div>
        </div>
    @endif
</div>
@endsection
