@extends('layouts.app')

@section('title', $event->title)

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@php
    $eventTypeName = 'Not set';
    $singleImageUrl = null;

    if ($event->eventType) {
        $eventTypeName = $event->eventType->name;
    }

    if ($event->photos->count() === 1 && ! $event->photos->first()->isVideo()) {
        $singleImageUrl = $event->photos->first()->fileUrl();
    }

    if ($event->photos->isEmpty() && $event->coverPhotoUrl()) {
        $singleImageUrl = $event->coverPhotoUrl();
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
                <form method="POST" action="{{ route('organizer.events.complete', $event) }}" class="organizer-confirm-form" data-confirm-title="Mark Event Completed" data-confirm-message="Mark this event as completed? This means the event has finished." data-confirm-button="Mark Completed">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn ph-btn-success btn-sm">Mark Event Completed</button>
                </form>
            @endif
            @if($canCancelEvent)
                <form method="POST" action="{{ route('organizer.events.cancel', $event) }}" class="organizer-confirm-form" data-confirm-title="Cancel Event" data-confirm-message="Cancel this event? Affected performers will be notified and active bookings will be cancelled." data-confirm-button="Cancel Event">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Cancel Event</button>
                </form>
            @endif
            <a href="{{ route('organizer.events.edit', $event) }}" class="btn ph-btn-primary btn-sm">Edit</a>
            <a href="{{ route('organizer.events.index') }}" class="btn ph-btn-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="ph-card p-0 overflow-hidden">
        @if($event->photos->count() > 1)
            <div class="organizer-event-detail-collage">
                @include('partials.event-photo-collage', ['photos' => $event->photos, 'title' => $event->title])
            </div>
        @elseif($event->photos->count() === 1)
            <div class="organizer-event-cover organizer-event-detail-cover">
                @if($event->photos->first()->isVideo())
                    <video controls preload="metadata"><source src="{{ $event->photos->first()->fileUrl() }}"></video>
                @else
                    <button type="button" class="organizer-media-preview-button" data-bs-toggle="modal" data-bs-target="#eventImagePreviewModal">
                        <img src="{{ $event->photos->first()->fileUrl() }}" alt="{{ $event->title }}">
                    </button>
                @endif
            </div>
        @elseif($event->coverPhotoUrl())
            <div class="organizer-event-cover organizer-event-detail-cover">
                <button type="button" class="organizer-media-preview-button" data-bs-toggle="modal" data-bs-target="#eventImagePreviewModal">
                    <img src="{{ $event->coverPhotoUrl() }}" alt="{{ $event->title }}">
                </button>
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
                @if(! empty($event->preferred_genres))
                    <div class="col-md-6">
                        <strong class="event-detail-label d-block mb-1">Preferred Genres</strong>
                        <span class="text-muted">
                            @foreach($event->preferred_genres as $genre)
                                {{ $genre }}@if(! $loop->last), @endif
                            @endforeach
                        </span>
                    </div>
                @endif
                @if($event->compensation_type === 'fixed' && $event->budget)
                    <div class="col-12">
                        <div class="organizer-budget-summary">
                            <div>
                                <strong>Fixed Budget</strong>
                                <span>PHP {{ number_format((float) $event->budget, 0) }}</span>
                            </div>
                            <div>
                                <strong>Allocated to Confirmed Bookings</strong>
                                <span>PHP {{ number_format($reservedBudget, 0) }}</span>
                            </div>
                            <div>
                                <strong>Remaining Budget</strong>
                                @if($remainingBudget < 0)
                                    <span class="text-danger">Over budget by PHP {{ number_format(abs($remainingBudget), 0) }}</span>
                                @else
                                    <span class="text-success">PHP {{ number_format($remainingBudget, 0) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                @if($event->compensation_type === 'hourly' && $event->rate_per_hour)
                    <div class="col-md-6"><strong class="event-detail-label d-block mb-1">Rate per Hour</strong><span class="text-muted">PHP {{ number_format((float) $event->rate_per_hour, 0) }}</span></div>
                @endif
                @if($event->compensation_type === 'contest')
                    <div class="col-md-6"><strong class="event-detail-label d-block mb-1">Contest Prizes</strong><span class="text-muted">First: PHP {{ number_format((float) $event->first_prize, 0) }}<br>Second: PHP {{ number_format((float) $event->second_prize, 0) }}<br>Third: PHP {{ number_format((float) $event->third_prize, 0) }}</span></div>
                @endif
            </div>
        </div>
</div>

@if($singleImageUrl)
    <div class="modal fade" id="eventImagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $event->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="{{ $singleImageUrl }}" alt="{{ $event->title }}" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
@endif

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
            $applicationStatusLabel = 'Pending';

            if ($application->status === 'invited') {
                $applicationStatusLabel = 'Booking request sent';
            }

            if ($application->status === 'accepted') {
                $applicationStatusLabel = 'Performer accepted';
            }

            if ($application->status === 'declined') {
                $applicationStatusLabel = 'Declined';
            }

            if ($application->status === 'cancelled') {
                $applicationStatusLabel = 'Event cancelled';
            }

            if ($application->status === 'invited') {
                $bookingMessage = 'Booking request sent - waiting for performer';
            }

            if ($application->status === 'accepted' && isset($bookings[$application->performer_id])) {
                $booking = $bookings[$application->performer_id];

                if ($booking->status === 'expired') {
                    $bookingMessage = 'Booking expired — event date passed before the contract was signed.';
                    $bookingMessageClass = 'text-danger';
                } elseif ($booking->status === 'completed') {
                    $bookingMessage = 'Booking confirmed';
                    $bookingMessageClass = 'text-success';
                } elseif ($booking->hasSignedContract()) {
                    $bookingMessage = 'Signed contract received';
                    $bookingMessageClass = 'text-primary';
                } elseif ($booking->hasContract()) {
                    $bookingMessage = 'Waiting for performer to sign the contract';
                    $bookingMessageClass = 'text-primary';
                } else {
                    $bookingMessage = 'Application accepted — upload the contract';
                    $bookingMessageClass = 'text-primary';
                }
            }
        @endphp
        <div class="ph-card p-3 mb-3 organizer-applicant-card">
            <div class="organizer-applicant-layout">
                <div class="d-flex align-items-center gap-3">
                    @if($applicantProfile)
                        <a href="{{ route('organizer.performers.show', $applicantProfile) }}">
                            <img src="{{ $applicant->avatarUrl(96) }}" alt="{{ $applicantName }}" class="rounded-circle" width="52" height="52">
                        </a>
                    @else
                        <img src="{{ $applicant->avatarUrl(96) }}" alt="{{ $applicantName }}" class="rounded-circle" width="52" height="52">
                    @endif

                    <div>
                        <h5 class="mb-1">
                            @if($applicantProfile)
                                <a href="{{ route('organizer.performers.show', $applicantProfile) }}" class="text-decoration-none text-dark">
                                    {{ $applicantName }}
                                </a>
                            @else
                                {{ $applicantName }}
                            @endif
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
                            @elseif($application->status == 'cancelled')
                                bg-secondary
                            @endif">
                            {{ $applicationStatusLabel }}
                        </span>

                        @if($bookingMessage)
                            <small class="{{ $bookingMessageClass }} d-block mt-2">{{ $bookingMessage }}</small>
                        @endif

                        @if($application->status === 'accepted' && isset($bookings[$application->performer_id]))
                            @if($bookings[$application->performer_id]->hasCancelRequest())
                                <small class="text-danger d-block mt-2">Cancellation requested - review the performer's reason.</small>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="organizer-applicant-actions">
                    @if($application->status === 'pending')
                        @if(auth()->user()->hasLimitedAccess())
                            <span class="btn ph-btn-secondary btn-sm disabled"><i class="fas fa-lock me-1"></i>Pending Verification</span>
                        @else
                            <a href="{{ route('organizer.bookings.create', ['performer' => $application->performer->performerProfile, 'event' => $event->id, 'from_application' => 1]) }}" class="btn ph-btn-primary btn-sm">
                                Accept Application
                            </a>
                            <form method="POST" action="{{ route('organizer.events.applications.decline', [$event, $application]) }}" class="organizer-confirm-form" data-confirm-title="Decline Applicant" data-confirm-message="Decline this applicant?" data-confirm-button="Decline Applicant">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">Decline</button>
                            </form>
                        @endif
                    @elseif($application->status === 'accepted' && isset($bookings[$application->performer_id]))
                        @php($booking = $bookings[$application->performer_id])
                        @if($booking->hasCancelRequest())
                            <a href="{{ route('organizer.bookings.show', $booking) }}" class="btn btn-outline-danger btn-sm">Review Cancellation</a>
                        @else
                            <a href="{{ route('organizer.bookings.show', $booking) }}" class="btn ph-btn-primary btn-sm">View Booking</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-secondary">No performers have applied yet.</div>
    @endforelse
</div>
@include('organizer.partials.confirmation-modal')
@endsection
