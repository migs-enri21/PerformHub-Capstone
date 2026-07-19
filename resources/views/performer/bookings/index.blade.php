@extends('layouts.app')

@section('title', 'Bookings')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Booking History</h2>


{{-- Add the filter here --}}
{{-- <form method="GET" class="ph-card p-3 mb-4 d-flex gap-2 align-items-end">
    <div>
        <label class="form-label">Booking Status</label>
        <select name="status" class="form-select ph-input">
            <option value="">All</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
            <option value="completed" @selected(request('status') === 'completed')>Completed</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
        </select>
    </div>

    <button type="submit" class="btn ph-btn-primary">Filter</button>
</form> --}}


<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead><tr><th>Event</th><th>Organizer</th><th>Date</th><th>Status</th><th>Contract</th><th></th></tr></thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
                    <td>{{ $booking->event_name }}</td>
                    <td>{{ $booking->organizer->organizerProfile?->organization_name ?? $booking->organizer->name }}</td>
                    <td>{{ $booking->event_date->format('M d, Y') }}</td>
                    <td><span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span></td>
                    <td>
                        @if($booking->status === 'accepted' || $booking->hasContract())
                            <span class="badge {{ $booking->contractStatusBadgeClass() }}">{{ $booking->contractStatusLabel(true) }}</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('performer.bookings.show', $booking) }}" class="btn btn-sm {{ $booking->needsContractReview() ? 'ph-btn-primary' : 'ph-btn-outline' }}">
                            {{ $booking->needsContractReview() ? 'Review' : 'View' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No bookings yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $bookings->links() }}
@endsection
