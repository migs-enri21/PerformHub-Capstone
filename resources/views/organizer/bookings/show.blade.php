@extends('layouts.app')

@section('title', $booking->event_name)

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@php
    $performerName = $booking->performer->name;
    $performerProfile = $booking->performer->performerProfile;

    if ($performerProfile && $performerProfile->stage_name) {
        $performerName = $performerProfile->stage_name;
    }

    $venue = 'TBD';
    $requirements = 'None';

    if ($booking->venue) {
        $venue = $booking->venue;
    }

    if ($booking->requirements) {
        $requirements = $booking->requirements;
    }

@endphp

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">{{ $booking->event_name }}</h2>
        <span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span>
    </div>
    <a href="{{ route('organizer.events.index') }}" class="btn ph-btn-outline btn-sm">Back to Events</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ph-card p-4 mb-4">
            <p><strong>Performer:</strong> {{ $performerName }}</p>
            <p><strong>Date:</strong> {{ $booking->event_date->format('F d, Y') }}</p>
            <p><strong>Venue:</strong> {{ $venue }}</p>
            <p class="mb-0"><strong>Requirements:</strong> {{ $requirements }}</p>
        </div>

        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-1">Contract Management</h5>
            <p class="text-muted small mb-3">Upload the contract. The performer can download it, sign it, and send the signed copy back.</p>

            @if($booking->hasContract())
                <a href="{{ $booking->contractUrl() }}" target="_blank" class="btn ph-btn-outline btn-sm mb-3">View Organizer Contract</a>
            @endif

            <form method="POST" action="{{ route('organizer.bookings.contract', $booking) }}" enctype="multipart/form-data" class="border-top pt-3">
                @csrf
                @if($booking->hasContract())
                    <input type="file" name="contract" class="form-control ph-input mb-2" accept=".pdf,.jpg,.jpeg,.png">
                @else
                    <input type="file" name="contract" class="form-control ph-input mb-2" accept=".pdf,.jpg,.jpeg,.png" required>
                @endif
                <small class="text-muted d-block mb-2">PDF, JPG, JPEG, or PNG. Maximum 10 MB.</small>
                @if($booking->hasContract())
                    <button class="btn ph-btn-primary btn-sm">Replace Contract</button>
                @else
                    <button class="btn ph-btn-primary btn-sm">Upload Contract</button>
                @endif
            </form>

            <hr>

            <h6 class="fw-semibold mb-2">Signed Contract from Performer</h6>
            @if($booking->hasSignedContract())
                <p class="text-success small mb-2">
                    The performer uploaded the signed copy.
                    @if($booking->signed_contract_uploaded_at)
                        Uploaded on {{ $booking->signed_contract_uploaded_at->format('M d, Y g:i A') }}.
                    @endif
                </p>
                <a href="{{ $booking->signedContractUrl() }}" target="_blank" class="btn ph-btn-outline btn-sm">View Signed Contract</a>
            @else
                <p class="text-muted small mb-0">Waiting for the performer to upload the signed contract.</p>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ph-card p-4 mb-4">
            <h5 class="fw-semibold mb-3">Booking Progress</h5>
            <p><span class="badge bg-success">Done</span> Booking Request Sent</p>
            <p>
                @if(in_array($booking->status, ['accepted', 'completed']))
                    <span class="badge bg-success">Done</span>
                @else
                    <span class="badge bg-secondary">Pending</span>
                @endif
                Performer Accepted
            </p>
            <p>
                @if($booking->hasContract())
                    <span class="badge bg-success">Done</span>
                @else
                    <span class="badge bg-secondary">Pending</span>
                @endif
                Contract Uploaded
            </p>
            <p>
                @if($booking->hasSignedContract())
                    <span class="badge bg-success">Done</span>
                @else
                    <span class="badge bg-secondary">Pending</span>
                @endif
                Signed Contract Returned
            </p>
            <p class="mb-0">
                @if($booking->status === 'completed')
                    <span class="badge bg-success">Done</span>
                @else
                    <span class="badge bg-secondary">Pending</span>
                @endif
                Booking Confirmed
            </p>
        </div>

        @if($booking->status === 'accepted')
            <div class="ph-card p-4">
                <h5 class="fw-semibold mb-3">Confirm Booking</h5>
                @if($booking->hasSignedContract())
                    <form method="POST" action="{{ route('organizer.bookings.complete', $booking) }}">
                        @csrf
                        <button class="btn btn-success w-100">Confirm Booking</button>
                    </form>
                @else
                    <p class="text-primary small mb-0">The performer must upload the signed contract first.</p>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
