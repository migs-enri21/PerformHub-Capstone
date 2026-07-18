@extends('layouts.app')

@section('title', 'View Event Type')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary mb-3">← Back to Categories</a>
    <h2 class="fw-bold">{{ $eventType->name }}</h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Event Type Details</h5>
            <div class="mb-3">
                <label class="form-label text-muted">Name</label>
                <p class="fw-bold">{{ $eventType->name }}</p>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Status</label>
                <p><span class="badge {{ $eventType->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $eventType->is_active ? 'Active' : 'Inactive' }}</span></p>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Description</label>
                <p>{{ $eventType->description ?? 'No description' }}</p>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Created</label>
                <p>{{ $eventType->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Last Updated</label>
                <p>{{ $eventType->updated_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="mt-4 pt-3 border-top">
                <a href="{{ route('admin.event-types.edit', $eventType) }}" class="btn ph-btn-primary">Edit</a>
                <form method="POST" action="{{ route('admin.event-types.destroy', $eventType) }}" class="d-inline" onsubmit="return confirm('Are you sure?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Event Type Notes</h5>
            <p class="text-muted">This event type is a standalone classification for events. Performers are not directly linked to this model yet.</p>
        </div>
    </div>
</div>
@endsection
