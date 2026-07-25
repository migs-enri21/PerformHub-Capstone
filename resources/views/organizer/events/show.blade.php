@extends('layouts.app')

@section('title', $event->title)

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $event->title }}</h2>
            <p class="text-muted mb-0">{{ ucfirst($event->status) }} event</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('organizer.events.edit', $event) }}" class="btn ph-btn-primary btn-sm">Edit</a>
            <a href="{{ route('organizer.events.index') }}" class="btn ph-btn-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="ph-card p-0 overflow-hidden">
        @if($event->photos->count() > 1)
            @include('partials.event-photo-collage', ['photos' => $event->photos, 'title' => $event->title])
        @elseif($event->photos->count() === 1)
            <div class="organizer-event-cover">
                <img src="{{ $event->photos->first()->fileUrl() }}" alt="{{ $event->title }}">
            </div>
        @elseif($event->coverPhotoUrl())
            <div class="organizer-event-cover">
                <img src="{{ $event->coverPhotoUrl() }}" alt="{{ $event->title }}">
            </div>
        @endif

        <div class="p-4">
        @if($event->description)
            <p class="text-muted">{{ $event->description }}</p>
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <strong class="text-white d-block mb-1">Date</strong>
                <span class="text-muted">{{ \Illuminate\Support\Carbon::parse($event->event_date)->format('F j, Y') }}</span>
            </div>
            <div class="col-md-6">
                <strong class="text-white d-block mb-1">Time</strong>
                <span class="text-muted">
                    {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}
                    @if($event->end_time)
                        – {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('g:i A') }}
                    @endif
                </span>
            </div>
            <div class="col-md-6">
                <strong class="text-white d-block mb-1">Venue</strong>
                <span class="text-muted">{{ $event->venue }}</span>
            </div>
            <div class="col-md-6">
                <strong class="text-white d-block mb-1">Event Type</strong>
                <span class="text-muted">{{ $event->eventType?->name ?? '—' }}</span>
            </div>
            @if($event->preferredCategory)
                <div class="col-md-6">
                    <strong class="text-white d-block mb-1">Preferred Category</strong>
                    <span class="text-muted">{{ $event->preferredCategory->name }}</span>
                </div>
            @endif
            @if($event->budget)
                <div class="col-md-6">
                    <strong class="text-white d-block mb-1">Budget</strong>
                    <span class="text-muted">₱{{ number_format((float) $event->budget, 0) }}</span>
                </div>
            @endif

            </div>
        </div>
    </div>
    <hr class="my-4">

    <h3 class="mb-3">Applicants ({{ $event->applications->count() }})</h3>

    @forelse($event->applications as $application)

    <div class="ph-card p-3 mb-3">

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">
                {{ $application->performer->performerProfile->stage_name ?? $application->performer->name }}
            </h5>

            <span class="badge
                @if($application->status == 'pending')
                bg-warning
                @elseif($application->status == 'invited')
                bg-info
                @elseif($application->status == 'accepted')
                bg-success
                @elseif($application->status == 'declined')
                bg-danger
                @endif">
                {{ ucfirst($application->status) }}
            </span>

        </div>
        <div>

            <a href="{{ route('organizer.bookings.create', ['performer' => $application->performer->performerProfile, 'event' => $event->id]) }}" class="btn ph-btn-primary">
                Send Booking
            </a>

        </div>

    </div>

</div>

@empty

<div class="alert alert-secondary"> No performers have applied yet.</div>

@endforelse    
</div>
@endsection