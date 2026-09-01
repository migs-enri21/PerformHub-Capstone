@extends('layouts.app')

@section('title', $event->title)

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@php
    $eventTypeName = 'Not set';

    if ($event->eventType) {
        $eventTypeName = $event->eventType->name;
    }
@endphp

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $event->title }}</h2>
            <p class="text-muted mb-0">{{ ucfirst($event->status) }} event</p>
        </div>
        <div class="d-flex gap-2">
            @if($canCompleteEvent)
                <form method="POST" action="{{ route('organizer.events.complete', $event) }}" onsubmit="return confirm('Mark this event as completed? This action means the event has finished.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-sm">Mark Event Completed</button>
                </form>
            @endif
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
                    <strong class="event-detail-label d-block mb-1">Date</strong>
                    <span class="text-muted">{{ \Illuminate\Support\Carbon::parse($event->event_date)->format('F j, Y') }}</span>
                </div>
                <div class="col-md-6">
                    <strong class="event-detail-label d-block mb-1">Time</strong>
                    <span class="text-muted">
                        {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}
                        @if($event->end_time)
                            - {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('g:i A') }}
                        @endif
                    </span>
                </div>
                <div class="col-md-6">
                    <strong class="event-detail-label d-block mb-1">Venue</strong>
                    <span class="text-muted">{{ $event->venue }}</span>
                </div>
                <div class="col-md-6">
                    <strong class="event-detail-label d-block mb-1">Event Type</strong>
                    <span class="text-muted">{{ $eventTypeName }}</span>
                </div>
                @if($event->categories->isNotEmpty())
                    <div class="col-md-6">
                        <strong class="event-detail-label d-block mb-1">Required Performer Categories</strong>
                        <span class="text-muted">
                            @foreach($event->categories as $category)
                                {{ $category->name }}@if(! $loop->last), @endif
                            @endforeach
                        </span>
                    </div>
                @endif
                @if($event->budget)
                    <div class="col-md-6">
                        <strong class="event-detail-label d-block mb-1">Budget</strong>
                        <span class="text-muted">PHP {{ number_format((float) $event->budget, 0) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <hr class="my-4">

    <h3 class="mb-3">Applicants ({{ $event->applications->count() }})</h3>

    @forelse($event->applications as $application)
        @php
            $applicant = $application->performer;
            $applicantName = $applicant->name;
            $applicantProfile = $applicant->performerProfile;

            if ($applicantProfile && $applicantProfile->stage_name) {
                $applicantName = $applicantProfile->stage_name;
            }

            $bookingMessage = null;
            $bookingMessageClass = 'text-muted';

            if ($application->status === 'invited') {
                $bookingMessage = 'Booking request sent - waiting for performer';
            }

            if ($application->status === 'accepted' && isset($bookings[$application->performer_id])) {
                $booking = $bookings[$application->performer_id];

                if ($booking->status === 'completed') {
                    $bookingMessage = 'Booking confirmed';
                    $bookingMessageClass = 'text-success';
                } elseif ($booking->hasSignedContract()) {
                    $bookingMessage = 'Signed contract received';
                    $bookingMessageClass = 'text-primary';
                } elseif ($booking->hasContract()) {
                    $bookingMessage = 'Performer accepted - waiting for signed contract';
                    $bookingMessageClass = 'text-primary';
                } else {
                    $bookingMessage = 'Performer accepted - upload contract';
                    $bookingMessageClass = 'text-primary';
                }
            }
        @endphp
        <div class="ph-card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        {{ $applicantName }}
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

                    @if($bookingMessage)
                        <small class="{{ $bookingMessageClass }} d-block mt-2">{{ $bookingMessage }}</small>
                    @endif
                </div>

                <div class="d-flex justify-content-end flex-wrap gap-2">
                    @if($application->status === 'pending')
                        <a href="{{ route('organizer.bookings.create', ['performer' => $application->performer->performerProfile, 'event' => $event->id]) }}" class="btn ph-btn-primary btn-sm">
                            Accept & Send Booking
                        </a>
                        <form method="POST" action="{{ route('organizer.events.applications.decline', [$event, $application]) }}" onsubmit="return confirm('Decline this applicant?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Decline</button>
                        </form>
                    @elseif($application->status === 'accepted' && isset($bookings[$application->performer_id]))
                        @php($booking = $bookings[$application->performer_id])
                        <a href="{{ route('organizer.bookings.show', $booking) }}" class="btn ph-btn-primary btn-sm">View Booking</a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-secondary">No performers have applied yet.</div>
    @endforelse
</div>
@endsection
