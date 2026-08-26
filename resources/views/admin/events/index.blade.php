@extends('layouts.app')

@section('title', 'User History Management')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">User History Management</h2>
</div>

<div class="ph-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.events.index') }}" class="d-flex gap-2">
        <input type="text" name="user_search" class="form-control ph-input flex-grow-1" placeholder="Search user..." value="{{ request('user_search') }}">
        <button type="submit" class="btn ph-btn-primary">Search</button>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Reset</a>
    </form>
</div>

<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Activity</th>
                <th>Event</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
                @php
                    $userName = $event->performer?->fullName() ?? $event->organizer?->fullName() ?? '—';
                    $activity = $event->status === 'accepted' ? 'Booking Accepted' : ($event->status === 'rejected' ? 'Booking Rejected' : 'Booking Updated');
                @endphp
                <tr>
                    <td>{{ $event->created_at->format('M d, Y') }}</td>
                    <td>{{ $userName }}</td>
                    <td>{{ $activity }}</td>
                    <td>{{ $event->event_name }}</td>
                    <td><span class="badge {{ $event->statusBadgeClass() }}">{{ $event->statusLabel() }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">No user activity found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $events->links() }}
</div>
@endsection
