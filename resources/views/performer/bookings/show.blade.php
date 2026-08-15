@extends('layouts.app')

@section('title', $booking->event_name)

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">{{ $booking->event_name }}</h2>
        <span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span>
    </div>
    @php($backUrl = request('from') === 'notifications' ? route('notifications.index') : route('performer.bookings.index'))
    <a href="{{ $backUrl }}" class="btn ph-btn-outline btn-sm">Back</a>
</div>

@if($booking->status === 'pending')
    <div class="ph-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">Respond to Booking</h5>
        <div class="d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('performer.bookings.accept', $booking) }}">@csrf<button class="btn ph-btn-primary">Accept Booking</button></form>
            <form method="POST" action="{{ route('performer.bookings.reject', $booking) }}">@csrf<button class="btn ph-btn-outline">Decline</button></form>
        </div>
    </div>
@endif

<div class="ph-card p-4 mb-4">
    <h5 class="fw-semibold mb-3">Event Details</h5>
    <p><strong>Date:</strong> {{ $booking->event_date->format('F d, Y') }} @if($booking->event_time) at {{ \Carbon\Carbon::parse($booking->event_time)->format('g:i A') }}@endif</p>
    <p><strong>Venue:</strong> {{ $booking->venue ?? 'TBD' }}</p>
    @if($booking->budget !== null)
        <p><strong>Budget Offer:</strong> ₱{{ number_format((float) $booking->budget, 2) }}</p>
    @endif
    <p><strong>Requirements:</strong> {{ $booking->requirements ?? 'None specified' }}</p>
    <p class="mb-0"><strong>Organizer:</strong> {{ $booking->organizer->organizerProfile?->organization_name ?? $booking->organizer->name }}</p>
</div>

@if(in_array($booking->status, ['accepted', 'completed']))
    <div class="ph-card p-4">
        <h5 class="fw-semibold mb-1">Contract</h5>

        @if($booking->hasContract())
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
        @else
            <p class="text-muted small mb-0">The organizer has not uploaded a contract yet.</p>
        @endif
    </div>
@endif
@endsection
