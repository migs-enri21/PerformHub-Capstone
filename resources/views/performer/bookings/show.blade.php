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
    @php
        if (request('from') === 'notifications') {
            $backUrl = route('notifications.index');
        } else {
            $backUrl = route('performer.bookings.index');
        }
    @endphp

<a href="{{ $backUrl }}" class="btn ph-btn-outline btn-sm booking-back-btn">
    <i class="fas fa-arrow-left me-1"></i> Back
</a>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        @if($booking->status === 'pending')
            <div class="ph-card p-4 mb-4">
                <h5 class="fw-semibold mb-3">Respond to Booking</h5>
                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('performer.bookings.accept', $booking) }}">
                        @csrf
                        <button type="submit" class="btn ph-btn-primary booking-accept-btn">Accept Booking</button>
                    </form>
                    <form method="POST" action="{{ route('performer.bookings.reject', $booking) }}">
                        @csrf
                        <button type="submit" class="btn ph-btn-outline booking-decline-btn">Decline</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="ph-card p-4 mb-4">
            <h5 class="fw-semibold mb-3">Event Details</h5>
            <p><strong>Date:</strong> {{ $booking->event_date->format('F d, Y') }} @if($booking->event_time) at {{ \Carbon\Carbon::parse($booking->event_time)->format('g:i A') }}@endif</p>
            <p><strong>Venue:</strong> {{ $booking->venue ?? 'TBD' }}</p>
            @php
                if ($booking->duration_hours) {
                    $durationLabel = $booking->duration_hours.' hours';
                } else {
                    $durationLabel = 'N/A';
                }
            @endphp
            <p><strong>Duration:</strong> {{ $durationLabel }}</p>
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

                    <a href="{{ $booking->contractUrl() }}" target="_blank" class="btn ph-btn-outline btn-sm mb-3">
                        <i class="fas fa-file me-1"></i> Review Contract File
                    </a>

                    @if($booking->hasSignedContract())
                        <div class="mb-3">
                            <a href="{{ $booking->signedContractUrl() }}" target="_blank" class="btn ph-btn-outline btn-sm">
                                <i class="fas fa-file-signature me-1"></i> View Your Signed Contract
                            </a>
                        </div>
                    @endif

                    @if(! $booking->performer_confirmed_contract)
                        <form method="POST" action="{{ route('performer.bookings.signed-contract', $booking) }}" enctype="multipart/form-data" class="border-top pt-3 mb-3" style="border-color: var(--ph-border) !important;">
                            @csrf
                            @if(! $booking->hasSignedContract())
                                <p class="small text-muted mb-2">Upload your signed contract before confirming.</p>
                            @else
                                <p class="small text-muted mb-2">Need to replace your signed copy? Upload a new one below.</p>
                            @endif
                            <input type="file" name="signed_contract" class="form-control ph-input mb-2" accept=".pdf,.jpg,.jpeg,.png" {{ $booking->hasSignedContract() ? '' : 'required' }}>
                            <small class="text-muted d-block mb-2">Accepted files: PDF, JPG, JPEG, or PNG. Maximum 10 MB.</small>
                            <button type="submit" class="btn ph-btn-outline btn-sm">
                                {{ $booking->hasSignedContract() ? 'Replace Signed Contract' : 'Upload Signed Contract' }}
                            </button>
                        </form>
                    @endif

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
</div>
@endsection
