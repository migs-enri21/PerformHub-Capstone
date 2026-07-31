@extends('layouts.app')

@section('title', $booking->event_name)

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">{{ $booking->event_name }}</h2>
        <span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span>
    </div>
    <a href="{{ route('organizer.bookings.index') }}" class="btn ph-btn-outline btn-sm">Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ph-card p-4 mb-4">
            <p><strong>Performer:</strong> {{ $booking->performer->performerProfile?->stage_name }}</p>
            <p><strong>Date:</strong> {{ $booking->event_date->format('F d, Y') }}</p>
            <p><strong>Venue:</strong> {{ $booking->venue ?? 'TBD' }}</p>
            <p><strong>Requirements:</strong> {{ $booking->requirements ?? 'None' }}</p>
        </div>
        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-1">Contract Management</h5>
            @if($booking->status === 'pending')
                <p class="text-muted small mb-3">Upload the contract now. The performer can review it after accepting the booking.</p>
            @else
                <p class="text-muted small mb-3">Upload the contract for the performer to review and confirm.</p>
            @endif

            @if($booking->hasContract())
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <a href="{{ $booking->contractUrl() }}" target="_blank" class="btn ph-btn-outline btn-sm">View Contract File</a>
                    <span class="badge {{ $booking->contractStatusBadgeClass() }}">{{ $booking->contractStatusLabel() }}</span>
                </div>
                @if($booking->performer_confirmed_contract)
                    <p class="text-success small mb-3">Performer confirmed on {{ $booking->contract_confirmed_at->format('M d, Y g:i A') }}.</p>
                @elseif($booking->status === 'accepted')
                    <p class="text-warning small mb-3">Waiting for the performer to review and confirm the contract.</p>
                @else
                    <p class="text-muted small mb-3">Contract saved. It will be available after the performer accepts.</p>
                @endif
            @endif

            <form method="POST" action="{{ route('organizer.bookings.contract', $booking) }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="contract" class="form-control ph-input mb-2" accept=".pdf,.jpg,.jpeg,.png" {{ $booking->hasContract() ? '' : 'required' }}>
                <small class="text-muted d-block mb-2">Accepted files: PDF, JPG, JPEG, or PNG. Maximum 10 MB.</small>
                <button class="btn ph-btn-primary btn-sm">{{ $booking->hasContract() ? 'Replace Contract' : 'Upload Contract' }}</button>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ph-card p-4 mb-4">
            <h5 class="fw-semibold mb-3">Booking Progress</h5>

            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-success">Done</span>
                <span>Booking Request Sent</span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3">
                @if($booking->status === 'accepted' || $booking->status === 'completed')
                    <span class="badge bg-success">Done</span>
                @elseif($booking->status === 'pending')
                    <span class="badge bg-warning text-dark">Current</span>
                @else
                    <span class="badge bg-secondary">Pending</span>
                @endif
                <span>Performer Accepted</span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3">
                @if($booking->hasContract())
                    <span class="badge bg-success">Done</span>
                @elseif($booking->status === 'accepted')
                    <span class="badge bg-warning text-dark">Current</span>
                @else
                    <span class="badge bg-secondary">Pending</span>
                @endif
                <span>Contract Uploaded</span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3">
                @if($booking->performer_confirmed_contract)
                    <span class="badge bg-success">Done</span>
                @elseif($booking->hasContract())
                    <span class="badge bg-warning text-dark">Current</span>
                @else
                    <span class="badge bg-secondary">Pending</span>
                @endif
                <span>Contract Confirmed</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                @if($booking->status === 'completed')
                    <span class="badge bg-success">Done</span>
                @else
                    <span class="badge bg-secondary">Pending</span>
                @endif
                <span>Booking Completed</span>
            </div>
        </div>

        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-3">Actions</h5>
            @if($booking->status === 'accepted')
                @if($booking->hasContract() && ! $booking->performer_confirmed_contract)
                    <p class="text-warning small mb-2">The performer must confirm the contract before you can mark this booking complete.</p>
                @endif
                <form method="POST" action="{{ route('organizer.bookings.complete', $booking) }}">@csrf<button class="btn btn-success w-100" @disabled($booking->hasContract() && ! $booking->performer_confirmed_contract)>Mark Completed</button></form>
            @endif
        </div>
    </div>
</div>
@endsection
