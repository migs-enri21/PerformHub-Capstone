@extends('layouts.app')

@section('title', $profile->organization_name)

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-end mb-2">
    <a href="{{ route('performer.dashboard') }}" class="btn ph-btn-outline btn-sm">Back to Dashboard</a>
</div>

@include('partials.organizer-profile-header', [
    'organizer' => $profile,
    'editable' => false,
])

<div class="row g-4">
    <div class="col-md-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-semibold mb-3">Contact Info</h5>
            <p class="text-muted mb-1">
                <i class="fas fa-phone me-2"></i>
                @if($profile->phone)
                    {{ $profile->phone }}
                @else
                    No phone number set.
                @endif
            </p>
            <p class="mb-0">
                <i class="fas fa-globe me-2 text-muted"></i>
                @if($profile->website)
                    <a href="{{ $profile->website }}" target="_blank" rel="noopener">{{ $profile->website }}</a>
                @else
                    <span class="text-muted">No website set.</span>
                @endif
            </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-semibold mb-3">Location</h5>
            <p class="text-muted mb-0">
                @if($profile->fullLocation())
                    {{ $profile->fullLocation() }}
                @else
                    No location set yet.
                @endif
            </p>
        </div>
    </div>
</div>

<div class="mt-4">
    <h5 class="fw-semibold mb-3">Events</h5>

    @if($events->isNotEmpty())
        <div class="row justify-content-center">
            <div class="col-lg-9">
            @foreach($events as $event)
                @php
                    $applicationStatus = null;
                    $bookingUrl = null;

                    if ($applicationStatuses->has($event->id)) {
                        $applicationStatus = $applicationStatuses->get($event->id);
                    }

                    if ($pendingBookingUrls->has($event->id)) {
                        $bookingUrl = $pendingBookingUrls->get($event->id);
                    }
                @endphp

                @include('partials.event-feed-post', [
                    'event' => $event,
                    'applicationStatus' => $applicationStatus,
                    'bookingUrl' => $bookingUrl,
                ])
            @endforeach
            </div>
        </div>
    @else
        <div class="ph-card p-4 text-muted">This organizer has not created any events yet.</div>
    @endif
</div>
@endsection
