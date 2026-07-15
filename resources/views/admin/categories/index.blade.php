@extends('layouts.app')

@section('title', 'Category & Event Management')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Category & Event Management</h2>
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
                <option value="active" {{ ($status === 'active') ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ ($status === 'inactive') ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-bold mb-3">Create Category</h5>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="row g-3">
                @csrf
                <div class="col-12"><input type="text" name="name" class="form-control ph-input" placeholder="Category name" required></div>
                <div class="col-12"><input type="text" name="description" class="form-control ph-input" placeholder="Description"></div>
                <div class="col-12"><button class="btn ph-btn-primary w-100">Add Category</button></div>
            </form>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-bold mb-3">Create Event Type</h5>
            <form method="POST" action="{{ route('admin.event-types.store') }}" class="row g-3">
                @csrf
                <div class="col-12"><input type="text" name="name" class="form-control ph-input" placeholder="Event type name" required></div>
                <div class="col-12"><input type="text" name="description" class="form-control ph-input" placeholder="Description"></div>
                <div class="col-12"><button class="btn ph-btn-primary w-100">Add Event Type</button></div>
            </form>
        </div>
    </div>
</div>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-bold mb-3">Categories</h5>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-center">Performers</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td class="align-middle"><strong>{{ $cat->name }}</strong></td>
                        <td class="align-middle"><p class="mb-0 text-truncate" style="max-width: 350px;">{{ $cat->description ?? '—' }}</p></td>
                        <td class="align-middle"><span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="align-middle text-center"><span class="badge bg-info">{{ $cat->performers->count() }}</span></td>
                        <td class="align-middle text-end">
                            <a href="{{ route('admin.categories.show', $cat) }}" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('admin.categories.toggle', $cat) }}" class="d-inline" style="display:inline-block;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $cat->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $cat->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas {{ $cat->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Delete this category?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
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
</div>

<div class="ph-card p-4">
    <h5 class="fw-bold mb-3">Event Types</h5>
    <div class="table-responsive">
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
                        <td><span class="badge {{ $eventType->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $eventType->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.event-types.show', $eventType) }}" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.event-types.edit', $eventType) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('admin.event-types.toggle', $eventType) }}" class="d-inline" style="display:inline-block;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $eventType->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $eventType->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas {{ $eventType->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.event-types.destroy', $eventType) }}" class="d-inline" onsubmit="return confirm('Delete this event type?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
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
        {{ $eventTypes->links('pagination::bootstrap-5') }}
    </div>
</div>

@endsection
