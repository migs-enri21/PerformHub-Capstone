@extends('layouts.app')

@section('title', 'Bookings')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Booking History</h2>
<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead><tr><th>Event</th><th>Organizer</th><th>Date</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td>{{ $booking->event_name }}</td>
                    <td>{{ $booking->organizer->organizerProfile?->organization_name ?? $booking->organizer->name }}</td>
                    <td>{{ $booking->event_date->format('M d, Y') }}</td>
                    <td><span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span></td>
                    <td><a href="{{ route('performer.bookings.show', $booking) }}" class="btn btn-sm ph-btn-outline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No bookings yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $bookings->links() }}
@endsection
