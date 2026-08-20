@extends('layouts.app')

@section('title', 'Organizer Dashboard')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@include('partials.onboarding-banner')

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">Home</h2>
        <p class="text-muted mb-0">Manage your events and discover talent.</p>
    </div>
    <a href="{{ route('organizer.events.create') }}" class="btn ph-btn-primary"><i class="fas fa-plus me-2"></i>Create Event</a>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="org-panel mb-4">
            <h5 class="fw-bold mb-3">Quick Overview</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="{{ route('organizer.events.index') }}" class="org-stat">
                        <i class="fas fa-calendar-plus"></i>
                        <div><strong>{{ $upcomingEvents->count() }}</strong><small>Upcoming Events</small></div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('organizer.events.index') }}" class="org-stat">
                        <i class="fas fa-clock"></i>
                        <div><strong>{{ $pendingBookings }}</strong><small>Pending Bookings</small></div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('organizer.events.index') }}" class="org-stat">
                        <i class="fas fa-check-circle"></i>
                        <div><strong>{{ $activeBookings }}</strong><small>Accepted Bookings</small></div>
                    </a>
                </div>
            </div>
        </div>

        <div class="org-panel mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Suggested Performers</h5>
                <a href="{{ route('organizer.performers.index') }}" class="small">Browse all</a>
            </div>

            @if($recommendationEvent)
                <p class="text-muted small mb-3">Based on your upcoming event: {{ $recommendationEvent->title }}</p>

                @forelse($recommendedPerformers as $performer)
                    <a href="{{ route('organizer.performers.show', $performer) }}" class="org-list-item">
                        @if($performer->profilePhotoUrl())
                            <img src="{{ $performer->profilePhotoUrl() }}" alt="{{ $performer->stage_name }}" class="rounded-circle" width="48" height="48">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($performer->stage_name) }}&background=6d3df5&color=fff" alt="{{ $performer->stage_name }}" class="rounded-circle" width="48" height="48">
                        @endif
                        <div class="flex-grow-1">
                            <strong>{{ $performer->stage_name }}</strong>
                            <span class="badge bg-success ms-1">Verified</span>
                            @if($performer->categoryNames())
                                <small class="text-muted d-block">{{ $performer->categoryNames() }}</small>
                            @else
                                <small class="text-muted d-block">Performer</small>
                            @endif
                            @if($performer->portfolios->count())
                                <small class="text-primary">{{ $performer->portfolios->count() }} portfolio {{ Str::plural('item', $performer->portfolios->count()) }}</small>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="text-muted mb-0">No verified performers match this event's categories yet.</p>
                @endforelse
            @else
                <p class="text-muted mb-0">Create an upcoming event to receive performer suggestions.</p>
            @endif
        </div>

        <section class="mb-4">
            <h5 class="fw-bold mb-3">Activity Feed</h5>

            <div class="portfolio-feed-stream">
                @forelse($feedPosts as $post)
                    @if($post['type'] === 'event')
                        @include('partials.event-activity-post', ['event' => $post['event']])
                    @else
                        @include('partials.portfolio-feed-post', [
                            'items' => $post['items'],
                            'performer' => $post['performer']
                        ])
                    @endif
                @empty
                    <div class="org-panel text-center text-muted">No activity yet.</div>
                @endforelse
            </div>
        </section>
    </div>

    <aside class="col-xl-4">
        <div class="org-right-column">
            <div class="org-panel mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Up Next</h6>
                    <a href="{{ route('organizer.events.index') }}" class="small">See all</a>
                </div>

                @if($upcomingEvents->isNotEmpty())
                    @php($nextEvent = $upcomingEvents->first())
                    <a href="{{ route('organizer.events.show', $nextEvent) }}" class="org-list-item">
                        <span class="org-event-date">{{ \Illuminate\Support\Carbon::parse($nextEvent->event_date)->format('d M') }}</span>
                        <div>
                            <strong>{{ $nextEvent->title }}</strong>
                            <small class="text-muted d-block">{{ $nextEvent->venue }}</small>
                        </div>
                    </a>
                @else
                    <p class="text-muted small mb-0">No upcoming events yet.</p>
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
