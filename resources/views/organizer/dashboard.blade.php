@extends('layouts.app')

@section('title', 'Organizer Dashboard')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@include('partials.onboarding-banner')

<h2 class="fw-bold mb-1">Welcome, {{ $profile?->organization_name ?? auth()->user()->name }}</h2>
<p class="text-muted mb-4">
    @if(auth()->user()->hasLimitedAccess())
        Manage your events and discover talent — complete sign-up to book performers.
    @else
        Manage your events and discover talent
    @endif
</p>

<div class="ph-card p-0 mb-4">
    <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
        <h5 class="mb-0 fw-bold">My Events</h5>

        <a href="{{ route('organizer.events.create') }}"class="btn btn-sm ph-btn-primary">
            + Create Event
        </a>
    </div>

    @forelse($myEvents as $event)

    <div class="p-4 border-bottom">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-start">

            <div>

                <h5 class="fw-bold mb-1">{{ $event->title }}</h5>
                <p class="text-muted mb-2"> {{ $event->eventType->name }}</p>

            </div>

            <span class="badge bg-warning text-dark">{{ ucfirst($event->status) }}</span>

        </div>

        <div class="row mt-3">

            <div class="col-md-4">
                <small class="text-muted">Date</small>
                <div>{{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}</div>
            </div>

            <div class="col-md-4">
                <small class="text-muted">Venue</small>
                <div>{{ $event->venue }}</div>
            </div>

            <div class="col-md-4">
                <small class="text-muted">Budget</small>
                <div>₱{{ number_format($event->budget ?? 0) }}</div>
            </div>

        </div>

        <div class="mt-4 d-flex gap-2">

            <a href="{{ route('organizer.performers.index', ['event' => $event->id]) }}" class="btn ph-btn-primary">Find Performers</a>
            <a href="{{ route('organizer.events.edit', $event) }}"class="btn ph-btn-secondary">View Event</a>

        </div>

    </div>
</div>

@empty
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body text-center py-5">
        <h5>No events yet</h5>
        <p class="text-muted">Create your first event to start finding performers.</p>

        <a href="{{ route('organizer.events.create') }}" class="btn ph-btn-primary">
            Create Event
        </a>
    </div>
</div>
@endforelse

<h4 class="fw-bold mb-3">Discover Performers</h4>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-semibold mb-3">Recommended for you</h5>
    <div class="row g-3">
        @forelse($recommendedPerformers as $p)
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:var(--ph-bg-input);">
                    <img src="{{ $p->profilePhotoUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($p->stage_name).'&background=6346ff&color=fff' }}" class="rounded-circle" width="48" height="48" style="object-fit:cover;">
                    <div class="flex-grow-1">
                        <h6 class="mb-0">{{ $p->stage_name }}</h6>
                        <small class="text-muted">{{ $p->categoryNames() }}</small>
                    </div>
                    <a href="{{ route('organizer.performers.show', $p) }}" class="btn btn-sm ph-btn-primary">View</a>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">No recommendations available.</p>
        @endforelse
    </div>
</div>

<a href="{{ route('organizer.performers.index') }}" class="btn ph-btn-primary">Browse All Performers</a>
@endsection
