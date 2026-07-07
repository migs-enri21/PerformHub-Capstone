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
    <form method="GET" action="{{ route('admin.events.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control ph-input" placeholder="Event name, venue, requirements" value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select ph-input">
                <option value="">All</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="interview_scheduled" @selected(request('status') === 'interview_scheduled')>Interview Scheduled</option>
                <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Organizer</label>
            <select name="organizer_id" class="form-select ph-input">
                <option value="">All Organizers</option>
                @foreach($organizers as $organizer)
                    <option value="{{ $organizer->id }}" @selected(request('organizer_id') == $organizer->id)>{{ $organizer->fullName() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control ph-input" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control ph-input" value="{{ request('date_to') }}">
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary">Filter</button>
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead>
            <tr>
                <th>Event</th>
                <th>Organizer</th>
                <th>Performer</th>
                <th>Date</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
                <tr>
                    <td>
                        <strong>{{ $event->event_name }}</strong>
                        @if($event->venue)<div class="small text-muted">{{ $event->venue }}</div>@endif
                    </td>
                    <td>{{ $event->organizer?->fullName() ?? '—' }}</td>
                    <td>{{ $event->performer?->fullName() ?? '—' }}</td>
                    <td>
                        {{ $event->event_date->format('M d, Y') }}
                        @if($event->event_time)<div class="small text-muted">{{ \Carbon\Carbon::parse($event->event_time)->format('g:i A') }}</div>@endif
                    </td>
                    <td><span class="badge {{ $event->statusBadgeClass() }}">{{ $event->statusLabel() }}</span></td>
                    <td>{{ $event->created_at->format('M d, Y h:i A') }}</td>
                    <td>
                        <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-outline-info">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">No events found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $events->links() }}
</div>
@endsection
