@extends('layouts.app')

@section('title', 'Edit Event Type')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary mb-3">← Back to Categories</a>
    <h2 class="fw-bold">Edit Event Type: {{ $eventType->name }}</h2>
</div>

<div class="ph-card p-4">
    <form method="POST" action="{{ route('admin.event-types.update', $eventType) }}" class="row g-3">
        @csrf
        @method('PUT')
        <div class="col-md-6">
            <label class="form-label">Event Type Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control ph-input @error('name') is-invalid @enderror" value="{{ old('name', $eventType->name) }}" required>
            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Active Status</label>
            <div class="form-check mt-2">
                <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $eventType->is_active) ? 'checked' : '' }} id="is_active">
                <label class="form-check-label" for="is_active">This event type is active</label>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-control ph-input @error('description') is-invalid @enderror">{{ old('description', $eventType->description) }}</textarea>
            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-12">
            <button type="submit" class="btn ph-btn-primary">Save Changes</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
