@extends('layouts.app')

@section('title', ($listFilter ?? null) === 'cancel' ? 'Cancel Requests' : 'Bookings')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
@php
    $heading = match ($listFilter ?? null) {
        'cancel' => 'Cancel Requests',
        'pending' => 'Pending Requests',
        'accepted' => 'Upcoming Bookings',
        'completed' => 'Booked',
        'cancelled' => 'Cancelled',
        'expired' => 'Expired',
        default => 'Booking History',
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">{{ $heading }}</h2>
</div>

 {{-- <div class="ph-card p-4 mb-4">
    <form method="GET" action="{{ route('performer.bookings.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="bookingStatusFilter">Status</label>
            <select name="status" id="bookingStatusFilter" class="form-select ph-input">
                <option value="" @selected(($listFilter ?? '') === '')>All</option>
                <option value="pending" @selected(($listFilter ?? '') === 'pending')>Pending</option>
                <option value="accepted" @selected(($listFilter ?? '') === 'accepted')>Upcoming</option>
                <option value="completed" @selected(($listFilter ?? '') === 'completed')>Booked</option>
                <option value="cancel" @selected(($listFilter ?? '') === 'cancel')>Cancel requests</option>
                <option value="cancelled" @selected(($listFilter ?? '') === 'cancelled')>Cancelled</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('performer.bookings.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
        </div>
    </form>
</div>
--}}

<div class="ph-card p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Organizer</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Contract</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    @php
                        $needsSignature = $booking->status === 'accepted'
                            && $booking->hasContract()
                            && ! $booking->isSigned()
                            && ! $booking->eventHasEnded();
                    @endphp
                    <tr>
                        <td>{{ $booking->event_name }}</td>
                        <td>{{ $booking->organizer->organizerProfile?->organization_name ?? $booking->organizer->name }}</td>
                        <td>{{ $booking->event_date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span>
                            @if($booking->hasCancelRequest())
                                <span class="badge bg-warning text-dark">Cancel pending</span>
                            @endif
                        </td>
                        <td>
                            @if($booking->hasSignedContract())
                                <span class="badge bg-success">Signed copy sent</span>
                            @elseif($booking->isSignWellCompleted())
                                <span class="badge bg-success">Signed</span>
                            @elseif($needsSignature)
                                <span class="badge bg-warning text-dark">Sign contract</span>
                            @elseif($booking->hasContract())
                                <span class="badge bg-info">Contract uploaded</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('performer.bookings.show', $booking) }}"
                                class="btn btn-sm {{ $needsSignature ? 'ph-btn-primary' : 'ph-btn-outline' }} booking-view-btn">
                                <i class="fas fa-eye me-1"></i> {{ $needsSignature ? 'Sign' : 'View' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            {{ ($listFilter ?? null) === 'cancel' ? 'No cancel requests.' : 'No bookings yet.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $bookings->links() }}
@endsection
