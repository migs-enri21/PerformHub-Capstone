@extends('layouts.app')

@section('title', 'View Category')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary mb-3">← Back to Categories &amp; Event</a>
    <h2 class="fw-bold">{{ $category->name }}</h2>
</div>

<div class="row">
    <!-- Category Details -->
    <div class="col-md-6">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Category Details</h5>
            
            <div class="mb-3">
                <label class="form-label text-muted">Name</label>
                <p class="fw-bold">{{ $category->name }}</p>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Status</label>
                <p>
                    <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Description</label>
                <p>{{ $category->description ?? 'No description' }}</p>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Created</label>
                <p>{{ $category->created_at->format('M d, Y H:i') }}</p>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Last Updated</label>
                <p>{{ $category->updated_at->format('M d, Y H:i') }}</p>
            </div>

            <div class="mt-4 pt-3 border-top">
                <a href="{{ route('admin.categories.edit', $category) }}" class="btn ph-btn-primary">Edit</a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">Delete</button>
            </div>
        </div>
    </div>

    <!-- Performers in this Category -->
    <div class="col-md-6">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Performers ({{ $category->performers->count() }})</h5>
            
            @if($category->performers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-dark table-hover">
                        <thead>
                            <tr><th>Performer</th><th>Email</th></tr>
                        </thead>
                        <tbody>
                            @foreach($category->performers as $performer)
                                <tr>
                                    <td>
                                        <strong>{{ $performer->user->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $performer->user->email ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">No performers in this category yet</p>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="deleteCategoryModalLabel">Delete category?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Are you sure you want to delete “{{ $category->name }}”?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

@endsection
