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
        @if($profile?->is_verified_badge)
            <p class="text-muted mb-0">
                <span class="verified-badge"><i class="fas fa-circle-check"></i> Verified Performer</span>
            </p>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="ph-card p-4 stat-card">
            <h3 class="fw-bold mb-0">{{ $pendingBookings }}</h3>
            <p class="text-muted mb-0 small">Pending Requests</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ph-card p-4 stat-card">
            <h3 class="fw-bold mb-0">{{ $upcomingBookings }}</h3>
            <p class="text-muted mb-0 small">Upcoming Bookings</p>
        </div>
    </div>
</div>
<div class="row g-4">
    <div class="col-xl-8">
        <div class="event-feed-section">

            @if($availableEvents->isNotEmpty())
                <div class="event-feed-center">
                    @foreach($availableEvents as $event)
                        @include('partials.event-feed-post', [
                            'event' => $event,
                            'applicationStatus' => $applicationStatuses[$event->id] ?? null,
                            'bookingUrl' => $pendingBookingUrls[$event->id] ?? null,
                        ])
                    @endforeach
                </div>
            @else
                <div class="event-feed-center">
                    <div class="event-feed-empty text-muted text-center">
                        No open events match your performer categories yet.
                    </div>
                </div>
            @endif
        </div>
    </div>

    <aside class="col-xl-4">
        <div class="org-right-column">
            <div class="org-panel mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Up Next</h6>
                    <a href="{{ route('performer.bookings.index') }}" class="small">See all</a>
                </div>

                @if($nextBooking)
                    <a href="{{ route('performer.bookings.show', $nextBooking) }}" class="org-list-item">
                        <span class="org-event-date">{{ $nextBooking->event_date->format('d M') }}</span>
                        <div>
                            <strong>{{ $nextBooking->event_name }}</strong>
                            <small class="text-muted d-block">{{ $nextBooking->venue ?? 'Venue TBD' }}</small>
                        </div>
                    </a>
                @else
                    <p class="text-muted small mb-0">No upcoming bookings yet.</p>
                @endif
            </div>

            <div class="org-panel mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0">Notifications</h6>
                    <a href="{{ route('notifications.index') }}" class="small">See all</a>
                </div>

                @forelse($recentNotifications as $notification)
                    @if($notification->link)
                        <a href="{{ $notification->link }}" class="org-list-item">
                    @else
                        <a href="{{ route('notifications.index') }}" class="org-list-item">
                    @endif
                            <i class="fas fa-bell text-primary"></i>
                            <div>
                                <strong>{{ $notification->title }}</strong>
                                <small class="text-muted d-block">{{ $notification->message }}</small>
                            </div>
                        </a>
                @empty
                    <p class="text-muted small mb-0">No new notifications.</p>
                @endforelse
            </div>
        </div>
    </aside>
</div>
@endsection