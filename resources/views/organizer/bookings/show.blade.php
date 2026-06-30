@extends('layouts.app')

@section('title', $booking->event_name)

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between mb-4">
    <div><h2 class="fw-bold mb-1">{{ $booking->event_name }}</h2><span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span></div>
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
            <h5 class="fw-semibold mb-3">Contract Management</h5>
            @if($booking->contract_path)
                <a href="{{ asset('storage/'.$booking->contract_path) }}" target="_blank" class="btn ph-btn-outline btn-sm mb-2">View Contract</a>
                @if($booking->performer_confirmed_contract)<p class="text-success small">Performer confirmed contract.</p>@endif
            @endif
            <form method="POST" action="{{ route('organizer.bookings.contract', $booking) }}" enctype="multipart/form-data" class="mt-2">
                @csrf
                <input type="file" name="contract" class="form-control ph-input mb-2" accept=".pdf,.doc,.docx" required>
                <button class="btn ph-btn-primary btn-sm">Upload Contract</button>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-3">Actions</h5>
            @if(in_array($booking->status, ['pending', 'accepted']))
                <a href="{{ route('organizer.interviews.create', $booking) }}" class="btn ph-btn-primary w-100 mb-2"><i class="fas fa-video me-1"></i> Schedule Interview</a>
            @endif
            @if($booking->interview)
                <a href="{{ route('interviews.join', $booking->interview) }}" class="btn ph-btn-outline w-100 mb-2">Join Interview</a>
            @endif
            @if($booking->status === 'accepted')
                <form method="POST" action="{{ route('organizer.bookings.complete', $booking) }}">@csrf<button class="btn btn-success w-100">Mark Completed</button></form>
            @endif
            <a href="{{ route('messages.show', $booking->performer) }}" class="btn ph-btn-outline w-100 mt-2">Message Performer</a>
        </div>
    </div>
</div>
@endsection
