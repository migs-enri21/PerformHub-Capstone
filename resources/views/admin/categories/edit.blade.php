@extends('layouts.app')

@section('title', 'Edit Category')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary mb-3">← Back to Categories</a>
    <h2 class="fw-bold">Edit Category: {{ $category->name }}</h2>
</div>

<div class="ph-card p-4">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="row g-3">
        @csrf
        @method('PUT')
        
        <div class="col-md-6">
            <label class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control ph-input @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Active Status</label>
            <div class="form-check mt-2">
                <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} id="is_active">
                <label class="form-check-label" for="is_active">
                    This category is active
                </label>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-control ph-input @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="col-12">
            <button type="submit" class="btn ph-btn-primary">Save Changes</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@if($category->performers->count() > 0)
<div class="ph-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Performers in this category ({{ $category->performers->count() }})</h5>
    <div class="table-responsive">
        <table class="table table-sm table-dark">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($category->performers as $performer)
                    <tr>
                        <td>{{ $performer->user->name ?? 'N/A' }}</td>
                        <td>{{ $performer->user->email ?? 'N/A' }}</td>
                        <td><span class="badge bg-info">Performer</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
