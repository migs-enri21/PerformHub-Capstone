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

<div class="row g-4">
    <div class="col-lg-6">
        <div class="ph-card p-4 h-100">
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
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteSpecialtyModal">Delete</button>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Specialty Notes</h5>
            <p class="mb-0">Specialties describe a performer’s specific skills or services. Keep each specialty clear and distinct to help organizers find the right performer.</p>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteSpecialtyModal" tabindex="-1" aria-labelledby="deleteSpecialtyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form method="POST" action="{{ route('admin.specialties.destroy', $specialty) }}" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="deleteSpecialtyModalLabel">Delete specialty?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Are you sure you want to delete “{{ $specialty->name }}”?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>
@endsection
