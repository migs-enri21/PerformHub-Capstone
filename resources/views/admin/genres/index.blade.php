@extends('layouts.app')

@section('title', 'Genre & Specialty Management')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Genre & Specialty Management</h2>
</div>

<p class="text-muted mb-4">These lists appear on performer profiles and organizer search. Category stays the role (Singer, Dancer). Specialty is the skill or instrument. Genre is the style.</p>

<div class="ph-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.genres.index') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control ph-input" placeholder="Search by name or description" value="{{ $search ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control ph-input">
                <option value="">All Status</option>
                <option value="active" {{ ($status === 'active') ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ ($status === 'inactive') ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('admin.genres.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-bold mb-3">Create Genre</h5>
            <p class="text-muted small">Style of performance — Rap, OPM, Cover Band. Not a role like Singer.</p>
            <form method="POST" action="{{ route('admin.genres.store') }}" class="row g-3">
                @csrf
                <div class="col-12"><input type="text" name="name" class="form-control ph-input" placeholder="Genre name" required></div>
                <div class="col-12"><input type="text" name="description" class="form-control ph-input" placeholder="Description"></div>
                <div class="col-12"><button class="btn ph-btn-primary w-100">Add Genre</button></div>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-bold mb-3">Create Specialty</h5>
            <p class="text-muted small">Skill or instrument — Bass, Vocals, Rap Vocals. Not a style like R&amp;B.</p>
            <form method="POST" action="{{ route('admin.specialties.store') }}" class="row g-3">
                @csrf
                <div class="col-12"><input type="text" name="name" class="form-control ph-input" placeholder="Specialty name" required></div>
                <div class="col-12"><input type="text" name="description" class="form-control ph-input" placeholder="Description"></div>
                <div class="col-12"><button class="btn ph-btn-primary w-100">Add Specialty</button></div>
            </form>
        </div>
    </div>
</div>

@include('admin.partials.named-option-table', [
    'heading' => 'Genres',
    'items' => $genres,
    'prefix' => 'genres',
    'itemLabel' => 'genre',
    'empty' => 'No genres found',
    'extraClass' => 'mb-4',
])

@include('admin.partials.named-option-table', [
    'heading' => 'Specialties',
    'items' => $specialties,
    'prefix' => 'specialties',
    'itemLabel' => 'specialty',
    'empty' => 'No specialties found',
])
@endsection
