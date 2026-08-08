@extends('layouts.app')

@section('title', 'Event History')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Event History</h2>
        <p class="text-muted mb-0">View your past, completed, and cancelled events.</p>
    </div>
    <a href="{{ route('organizer.events.index') }}" class="btn ph-btn-outline btn-sm">View Active Events</a>
</div>

<div class="ph-card p-0 overflow-hidden">
    <table class="table table-hover mb-0">
        <thead><tr><th>Event</th><th>Date</th><th>Venue</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse($events as $event)
                <tr>
                    <td>{{ $event->title }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($event->event_date)->format('M d, Y') }}</td>
                    <td>{{ $event->venue }}</td>
                    <td><span class="badge {{ strtolower($event->status) === 'cancelled' ? 'bg-danger' : 'bg-secondary' }}">{{ $event->status }}</span></td>
                    <td><a href="{{ route('organizer.events.show', $event) }}" class="btn btn-sm ph-btn-outline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No event history yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $events->links() }}

@endsection
