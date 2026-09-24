@extends('layouts.app')

@section('title', 'Cancellation Requests')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">Cancellation Requests</h2>
        <p class="text-muted mb-0">Review performers who requested to cancel an active booking.</p>
    </div>
    <a href="{{ route('organizer.dashboard') }}" class="btn ph-btn-outline btn-sm">Back to Dashboard</a>
</div>

<div class="org-panel">
    @forelse($requests as $booking)
        @php
            $performer = $booking->performer;
            $performerProfile = $performer->performerProfile;
            $performerName = $performer->name;

            if ($performerProfile && $performerProfile->stage_name) {
                $performerName = $performerProfile->stage_name;
            }
        @endphp

        <div class="org-list-item py-3">
            @if($performerProfile && $performerProfile->profilePhotoUrl())
                <img src="{{ $performerProfile->profilePhotoUrl() }}" alt="{{ $performerName }}" class="rounded-circle" width="52" height="52">
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($performerName) }}&background=6d3df5&color=fff" alt="{{ $performerName }}" class="rounded-circle" width="52" height="52">
            @endif

            <div class="flex-grow-1">
                <strong>{{ $performerName }}</strong>
                <small class="text-muted d-block">{{ $booking->event_name }} · {{ $booking->event_date->format('F d, Y') }}</small>
                <small class="text-muted d-block mt-1">Requested {{ $booking->cancel_requested_at->diffForHumans() }}</small>
                <p class="small mb-0 mt-2"><strong>Reason:</strong> {{ $booking->cancel_reason }}</p>
            </div>

            <a href="{{ route('organizer.bookings.show', $booking) }}" class="btn ph-btn-primary btn-sm">Review Request</a>
        </div>
    @empty
        <div class="text-center py-4">
            <i class="fas fa-check-circle text-success fs-3 mb-2"></i>
            <p class="text-muted mb-0">There are no pending cancellation requests.</p>
        </div>
    @endforelse
</div>
@endsection
