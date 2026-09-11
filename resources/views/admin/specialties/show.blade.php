@extends('layouts.app')

@section('title', 'View Specialty')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.genres.index') }}" class="btn btn-outline-secondary mb-3">← Back to Genres & Specialties</a>
    <h2 class="fw-bold">{{ $specialty->name }}</h2>
</div>

<div class="ph-card p-4">
    <h5 class="fw-bold mb-3">Specialty Details</h5>
    <div class="mb-3">
        <label class="form-label text-muted">Name</label>
        <p class="fw-bold">{{ $specialty->name }}</p>
    </div>
    <div class="mb-3">
        <label class="form-label text-muted">Status</label>
        <p><span class="badge {{ $specialty->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $specialty->is_active ? 'Active' : 'Inactive' }}</span></p>
    </div>
    <div class="mb-3">
        <label class="form-label text-muted">Description</label>
        <p>{{ $specialty->description ?? 'No description' }}</p>
    </div>
    <div class="mb-3">
        <label class="form-label text-muted">Created</label>
        <p>{{ $specialty->created_at->format('M d, Y H:i') }}</p>
    </div>
    <div class="mb-3">
        <label class="form-label text-muted">Last Updated</label>
        <p>{{ $specialty->updated_at->format('M d, Y H:i') }}</p>
    </div>
    <div class="mt-4 pt-3 border-top">
        <a href="{{ route('admin.specialties.edit', $specialty) }}" class="btn ph-btn-primary">Edit</a>
        <form method="POST" action="{{ route('admin.specialties.destroy', $specialty) }}" class="d-inline" onsubmit="return confirm('Are you sure?');">
            @csrf @method('DELETE')
            <button class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>
@endsection
