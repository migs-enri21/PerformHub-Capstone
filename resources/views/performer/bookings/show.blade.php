@extends('layouts.app')

@section('title', $booking->event_name)

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
@php
    $backUrl = request('from') === 'notifications'
        ? route('notifications.index')
        : route('performer.bookings.index');
@endphp

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">{{ $booking->event_name }}</h2>
        <span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span>
    </div>
    <a href="{{ $backUrl }}" class="btn ph-btn-outline btn-sm">Back</a>
</div>

@if($booking->status === 'pending')
    <div class="ph-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">Respond to Booking</h5>
        @if($dayConflict)
            <div class="alert alert-warning small mb-3">
                You already have <strong>{{ $dayConflict->event_name }}</strong> on
                {{ $booking->event_date->format('F d, Y') }}. PerformHub allows <strong>1 event per day</strong>,
                so this request cannot be accepted. Decline it, or free that date first.
            </div>
        @endif
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('performer.bookings.accept', $booking) }}">
                @csrf
                <button class="btn ph-btn-primary" @disabled($dayConflict)>Accept Booking</button>
            </form>
            <form method="POST" action="{{ route('performer.bookings.reject', $booking) }}">
                @csrf
                <button class="btn ph-btn-outline">Decline</button>
            </form>
        </div>
    </div>
@endif

<div class="ph-card p-4 mb-4">
    <h5 class="fw-semibold mb-3">Event Details</h5>
    <p>
        <strong>Date:</strong>
        {{ $booking->event_date->format('F d, Y') }}
        @if($booking->event_time)
            at {{ \Carbon\Carbon::parse($booking->event_time)->format('g:i A') }}
        @endif
    </p>
    <p><strong>Venue:</strong> {{ $booking->venue ?? 'TBD' }}</p>
    @if($booking->budget !== null)
        <p><strong>Budget Offer:</strong> ₱{{ number_format((float) $booking->budget, 2) }}</p>
    @endif
    <p><strong>Requirements:</strong> {{ $booking->requirements ?? 'None specified' }}</p>
    <p class="mb-0">
        <strong>Organizer:</strong>
        {{ $booking->organizer->organizerProfile?->organization_name ?? $booking->organizer->name }}
    </p>
</div>

@if(in_array($booking->status, ['accepted', 'completed'], true))
    <div class="ph-card p-4">
        <h5 class="fw-semibold mb-1">Contract</h5>

        @if($booking->hasContract())
            @if($booking->signwell_document_id)
                <p class="text-muted small mb-3">The organizer uploaded a contract for electronic signature. Sign it here in PerformHub.</p>
                <a href="{{ $booking->contractUrl() }}" target="_blank" class="btn ph-btn-outline btn-sm mb-3">View Contract</a>

                @if($booking->hasSignedContract())
                    <p class="text-success small mb-2">Your electronic signature is complete.</p>
                    <a href="{{ $booking->signedContractUrl() }}" target="_blank" class="btn ph-btn-outline btn-sm">View Signed Contract</a>
                @elseif($booking->isSignWellCompleted())
                    <p class="text-success small mb-2">Your electronic signature is complete.</p>
                @else
                    <p class="text-primary small mb-2">SignWell status: {{ ucfirst($booking->signwell_status) }}</p>
                    @if($booking->status === 'accepted')
                        <a href="{{ route('performer.bookings.sign', $booking) }}" class="btn ph-btn-primary btn-sm">Sign Contract</a>
                    @endif
                @endif
            @else
                <p class="text-muted small mb-3">Download the organizer's contract, sign it, then upload the signed copy below.</p>
                <a href="{{ $booking->contractUrl() }}" target="_blank" class="btn ph-btn-outline btn-sm mb-3">Download Contract</a>

                @if($booking->hasSignedContract())
                    <p class="text-success small mb-2">Your signed contract was sent to the organizer.</p>
                    <a href="{{ $booking->signedContractUrl() }}" target="_blank" class="btn ph-btn-outline btn-sm">View Signed Contract</a>
                @elseif($booking->status === 'accepted')
                    <form method="POST" action="{{ route('performer.bookings.signed-contract', $booking) }}" enctype="multipart/form-data" class="border-top pt-3">
                        @csrf
                        <label class="form-label small">Upload Signed Contract</label>
                        <input type="file" name="signed_contract" class="form-control ph-input mb-2" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted d-block mb-2">PDF, JPG, JPEG, or PNG. Maximum 10 MB.</small>
                        <button class="btn ph-btn-primary btn-sm">Send Signed Contract</button>
                    </form>
                @endif
            @endif
        @else
            <p class="text-muted small mb-0">
                @if($booking->cameFromApplication())
                    The organizer accepted your application. Wait for them to upload the contract, then you can send the signed copy here.
                @else
                    Wait for the organizer to upload the contract, then you can send the signed copy here.
                @endif
            </p>
        @endif

        @if($booking->canRequestCancel())
            <div class="border-top pt-3 mt-3">
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelBookingModal">
                    Request cancel
                </button>
            </div>
        @elseif($booking->hasCancelRequest())
            <div class="border-top pt-3 mt-3">
                <span class="badge bg-warning text-dark">Cancel pending</span>
            </div>
        @endif
    </div>
@endif

@if($booking->canRequestCancel())
    <div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('performer.bookings.cancel-request', $booking) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Request cancel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">The organizer must approve this. This booking stays booked until they do.</p>
                    <label class="form-label" for="cancel_reason">Reason</label>
                    <textarea name="cancel_reason" id="cancel_reason" class="form-control ph-input" rows="3" required maxlength="500" placeholder="Why do you need to cancel?"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Send Request</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
