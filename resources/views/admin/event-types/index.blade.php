@extends('layouts.app')

@section('title', 'Event Type Management')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Event Type Management</h2>
</div>

<div class="ph-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.event-types.index') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control ph-input" placeholder="Search by name or description" value="{{ $search ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select ph-input">
                <option value="">All Status</option>
                <option value="active" {{ (request('status') === 'active') ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ (request('status') === 'inactive') ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('admin.event-types.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
        </div>
    </form>
</div>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-bold mb-3">Create Event Type</h5>
    <form method="POST" action="{{ route('admin.event-types.store') }}" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-4">
            <input type="text" name="name" class="form-control ph-input" placeholder="Event type name" required>
        </div>
        <div class="col-md-5">
            <input type="text" name="description" class="form-control ph-input" placeholder="Description">
        </div>
        <div class="col-md-3">
            <button class="btn ph-btn-primary w-100">Add Event Type</button>
        </div>
    </form>
</div>

<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($eventTypes as $eventType)
                <tr>
                    <td>{{ $eventType->name }}</td>
                    <td>{{ $eventType->description ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $eventType->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $eventType->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.event-types.show', $eventType) }}" class="btn btn-sm btn-outline-info">View</a>
                        <a href="{{ route('admin.event-types.edit', $eventType) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                        <form method="POST" action="{{ route('admin.event-types.toggle', $eventType) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $eventType->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $eventType->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas {{ $eventType->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                        </form>
                        <form method="POST" action="{{ route('admin.event-types.destroy', $eventType) }}" class="d-inline" onsubmit="return confirm('Delete this event type?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">No event types found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $eventTypes->links() }}
</div>
@endsection
