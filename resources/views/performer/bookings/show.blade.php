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

        @if($booking->contract_path)
            <div class="ph-card p-4">
                <h5 class="fw-semibold mb-3">Contract</h5>
                <a href="{{ asset('storage/'.$booking->contract_path) }}" target="_blank" class="btn ph-btn-outline btn-sm mb-3"><i class="fas fa-file-pdf me-1"></i> View Contract</a>
                @if(!$booking->performer_confirmed_contract)
                    <form method="POST" action="{{ route('performer.bookings.confirm-contract', $booking) }}">@csrf
                        <button class="btn ph-btn-primary btn-sm">Confirm Contract</button>
                    </form>
                @else
                    <p class="text-success small mb-0"><i class="fas fa-check-circle"></i> Contract confirmed on {{ $booking->contract_confirmed_at->format('M d, Y') }}</p>
                @endif
            </div>
        @endif
    </div>
    <div class="col-lg-4">
        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-3">Actions</h5>
            @if($booking->status === 'pending')
                <form method="POST" action="{{ route('performer.bookings.accept', $booking) }}" class="mb-2">@csrf<button class="btn ph-btn-primary w-100">Accept</button></form>
                <form method="POST" action="{{ route('performer.bookings.reject', $booking) }}">@csrf<button class="btn btn-outline-danger w-100">Reject</button></form>
            @elseif(in_array($booking->status, ['interview_scheduled', 'pending']))
                <form method="POST" action="{{ route('performer.bookings.reject', $booking) }}">@csrf<button class="btn btn-outline-danger w-100">Reject</button></form>
            @endif
            @if($booking->interview)
                <a href="{{ route('interviews.join', $booking->interview) }}" class="btn ph-btn-primary w-100 mt-2"><i class="fas fa-video me-1"></i> Join Interview</a>
            @endif
            <a href="{{ route('messages.show', $booking->organizer) }}" class="btn ph-btn-outline w-100 mt-2">Message Organizer</a>
        </div>
    </div>
</div>
@endsection
