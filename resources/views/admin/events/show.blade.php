@extends('layouts.app')

@section('title', 'Event Details')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">← Back to Events</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ph-card p-4">
            <h2 class="fw-bold mb-2">{{ $booking->event_name }}</h2>
            <p class="text-muted mb-3">Event details and booking information.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Organizer</div>
                    <div class="fw-semibold">{{ $booking->organizer?->fullName() ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Performer</div>
                    <div class="fw-semibold">{{ $booking->performer?->fullName() ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Event Date</div>
                    <div class="fw-semibold">{{ $booking->event_date->format('F d, Y') }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Event Time</div>
                    <div class="fw-semibold">{{ $booking->event_time ? \Carbon\Carbon::parse($booking->event_time)->format('g:i A') : '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Venue</div>
                    <div class="fw-semibold">{{ $booking->venue ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Status</div>
                    <span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Created</div>
                    <div class="fw-semibold">{{ $booking->created_at->format('F d, Y h:i A') }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Last Updated</div>
                    <div class="fw-semibold">{{ $booking->updated_at->format('F d, Y h:i A') }}</div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Requirements</div>
                    <div class="fw-semibold">{{ $booking->requirements ?? '—' }}</div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Notes</div>
                    <div class="fw-semibold">{{ $booking->notes ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Event Summary</h5>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Name</span><span>{{ $booking->event_name }}</span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Date Created</span><span>{{ $booking->created_at->format('M d, Y') }}</span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Scheduled Date</span><span>{{ $booking->event_date->format('M d, Y') }}</span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Created By</span><span>{{ $booking->organizer?->fullName() ?? '—' }}</span></div>
        </div>
    </div>
</div>
@endsection
