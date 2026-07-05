@extends('layouts.app')

@section('title', 'Categories')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Category Management</h2>
</div>

<!-- Filter Section -->
<div class="ph-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.categories.index') }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control ph-input" placeholder="Search by name or description" value="{{ $search ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control ph-input">
                <option value="">All Status</option>
                <option value="active" {{ (request('status') === 'active') ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ (request('status') === 'inactive') ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
        </div>
    </form>
</div>

<!-- Add New Category Form -->
<div class="ph-card p-4 mb-4">
    <form method="POST" action="{{ route('admin.categories.store') }}" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-3"><input type="text" name="name" class="form-control ph-input" placeholder="Category name" required></div>
        <div class="col-md-3"><input type="text" name="icon" class="form-control ph-input" placeholder="fa-music"></div>
        <div class="col-md-4"><input type="text" name="description" class="form-control ph-input" placeholder="Description"></div>
        <div class="col-md-2"><button class="btn ph-btn-primary w-100">Add Category</button></div>
    </form>
</div>

<!-- Categories Table -->
<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead>
            <tr>
                <th class="w-20">Name</th>
                <th class="w-10">Icon</th>
                <th class="w-15">Status</th>
                <th class="w-12 text-center">Performers</th>
                <th class="w-43 text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $cat)
                <tr>
                    <td class="align-middle">
                        <strong>{{ $cat->name }}</strong>
                    </td>
                    <td class="align-middle text-center">
                        <i class="fas {{ $cat->icon ?? 'fa-star' }} fa-lg" style="width: 30px; display: inline-block;"></i>
                    </td>
                    <td class="align-middle">
                        <span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $cat->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="badge bg-info">{{ $cat->performers->count() }}</span>
                    </td>
                    <td class="align-middle text-end">
                        <a href="{{ route('admin.categories.show', $cat) }}" class="btn btn-sm btn-outline-info" title="View">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('admin.categories.toggle', $cat) }}" class="d-inline" style="display:inline-block;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $cat->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $cat->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas {{ $cat->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Delete this category?');" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                        No categories found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $categories->links() }}
</div>

@endsection
