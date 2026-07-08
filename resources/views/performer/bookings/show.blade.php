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
        @if($booking->needsContractReview())
            <span class="badge bg-warning text-dark ms-1">Contract needs review</span>
        @endif
    </div>
    <a href="{{ route('performer.bookings.index') }}" class="btn ph-btn-outline btn-sm">Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ph-card p-4 mb-4">
            <h5 class="fw-semibold mb-3">Event Details</h5>
            <p><strong>Date:</strong> {{ $booking->event_date->format('F d, Y') }} @if($booking->event_time) at {{ \Carbon\Carbon::parse($booking->event_time)->format('g:i A') }}@endif</p>
            <p><strong>Venue:</strong> {{ $booking->venue ?? 'TBD' }}</p>
            <p><strong>Duration:</strong> {{ $booking->duration_hours ? $booking->duration_hours.' hours' : 'N/A' }}</p>
            <p><strong>Requirements:</strong> {{ $booking->requirements ?? 'None specified' }}</p>
            <p class="mb-0"><strong>Organizer:</strong> {{ $booking->organizer->organizerProfile?->organization_name ?? $booking->organizer->name }}</p>
        </div>

        @if($booking->status === 'accepted' || ($booking->hasContract() && $booking->status === 'completed'))
            <div class="ph-card p-4">
                <h5 class="fw-semibold mb-1">Contract</h5>
                <p class="text-muted small mb-3">
                    @if($booking->hasContract())
                        Review the contract file from the organizer, then confirm if you agree with the terms.
                    @else
                        The organizer has not uploaded a contract yet. You will be notified when one is ready.
                    @endif
                </p>

                @if($booking->hasContract())
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge {{ $booking->contractStatusBadgeClass() }}">{{ $booking->contractStatusLabel(true) }}</span>
                        @if($booking->performer_confirmed_contract)
                            <small class="text-muted">Confirmed on {{ $booking->contract_confirmed_at->format('M d, Y g:i A') }}</small>
                        @endif
                    </div>

                    <a href="{{ asset('storage/'.$booking->contract_path) }}" target="_blank" class="btn ph-btn-outline btn-sm mb-3">
                        <i class="fas fa-file-pdf me-1"></i> Review Contract
                    </a>

                    @if($booking->canConfirmContract())
                        <form method="POST" action="{{ route('performer.bookings.confirm-contract', $booking) }}" class="border-top pt-3" style="border-color: var(--ph-border) !important;">
                            @csrf
                            <p class="small text-muted mb-2">By confirming, you agree to the contract terms for this booking.</p>
                            <button type="submit" class="btn ph-btn-primary btn-sm">
                                <i class="fas fa-check-circle me-1"></i> Confirm Contract
                            </button>
                        </form>
                    @elseif($booking->performer_confirmed_contract)
                        <p class="text-success small mb-0"><i class="fas fa-check-circle me-1"></i> You have confirmed this contract.</p>
                    @endif
                @endif
            </div>
        @endif
    </div>
    <div class="col-lg-4">
        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-3">Actions</h5>
            @if($booking->status === 'pending')
                <form method="POST" action="{{ route('performer.bookings.accept', $booking) }}" class="mb-2">@csrf<button class="btn ph-btn-primary w-100">Accept Booking</button></form>
                <form method="POST" action="{{ route('performer.bookings.reject', $booking) }}">@csrf<button class="btn btn-outline-danger w-100">Reject</button></form>
            @elseif($booking->status === 'interview_scheduled')
                <form method="POST" action="{{ route('performer.bookings.reject', $booking) }}">@csrf<button class="btn btn-outline-danger w-100">Reject</button></form>
            @endif
            @if($booking->needsContractReview())
                <a href="{{ asset('storage/'.$booking->contract_path) }}" target="_blank" class="btn ph-btn-primary w-100 mb-2">
                    <i class="fas fa-file-signature me-1"></i> Review Contract
                </a>
            @endif
            @if($booking->interview)
                <a href="{{ route('interviews.join', $booking->interview) }}" class="btn ph-btn-primary w-100 mt-2"><i class="fas fa-video me-1"></i> Join Interview</a>
            @endif
        </div>
    </div>
</div>
@endsection
