@extends('layouts.app')

@section('title', 'Monitor Bookings')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Booking Records</h2>
<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead><tr><th>Event</th><th>Organizer</th><th>Performer</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($bookings as $b)
                <tr>
                    <td>{{ $b->event_name }}</td>
                    <td>{{ $b->organizer->name }}</td>
                    <td>{{ $b->performer->name }}</td>
                    <td>{{ optional($b->event_date)->format('M d, Y') }}</td>
                    <td><span class="badge {{ $b->statusBadgeClass() }}">{{ $b->statusLabel() }}</span></td>
                    <td><a href="{{ route('admin.events.show', $b) }}" class="btn btn-sm btn-outline-info">Preview</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $bookings->links() }}
@endsection
