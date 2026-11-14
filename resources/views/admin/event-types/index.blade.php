@extends('layouts.app')

@section('title', 'Event Type Management')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Event Type Management</h2>
    <button type="button" class="btn ph-btn-primary" data-bs-toggle="modal" data-bs-target="#createEventTypeModal">
        <i class="fas fa-plus me-1"></i> Add Event Type
    </button>
</div>

<div class="ph-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.event-types.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control ph-input" placeholder="Search by name or description" value="{{ $search ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select ph-input">
                <option value="">All Status</option>
                <option value="active" {{ (request('status') === 'active') ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ (request('status') === 'inactive') ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        @include('admin.partials.sort-filter', ['sortByLabel' => 'Name', 'defaultSort' => 'name_asc'])
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('admin.event-types.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
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
                        <button type="button" class="btn btn-sm btn-outline-warning" title="Edit"
                            data-bs-target="#editEventTypeModal"
                            data-edit-action="{{ route('admin.event-types.update', $eventType) }}"
                            data-name="{{ $eventType->name }}" data-description="{{ $eventType->description }}"
                            data-active="{{ $eventType->is_active ? '1' : '0' }}">Edit</button>
                        <form method="POST" action="{{ route('admin.event-types.toggle', $eventType) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $eventType->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $eventType->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas {{ $eventType->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                        </form>
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                            data-bs-target="#deleteEventTypeModal"
                            data-delete-action="{{ route('admin.event-types.destroy', $eventType) }}"
                            data-delete-name="{{ $eventType->name }}">
                            <i class="fas fa-trash"></i>
                        </button>
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

<div class="modal fade" id="createEventTypeModal" tabindex="-1" aria-labelledby="createEventTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.event-types.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="_modal" value="event-type-create">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="createEventTypeModalLabel">Add Event Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="newEventTypeName" class="form-label">Event type name <span class="text-danger">*</span></label>
                <input id="newEventTypeName" type="text" name="name" class="form-control ph-input mb-3 {{ old('_modal') === 'event-type-create' && $errors->has('name') ? 'is-invalid' : '' }}"
                    value="{{ old('_modal') === 'event-type-create' ? old('name') : '' }}" required maxlength="100">
                @if(old('_modal') === 'event-type-create') @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                <label for="newEventTypeDescription" class="form-label">Description</label>
                <textarea id="newEventTypeDescription" name="description" rows="3" maxlength="500" class="form-control ph-input {{ old('_modal') === 'event-type-create' && $errors->has('description') ? 'is-invalid' : '' }}">{{ old('_modal') === 'event-type-create' ? old('description') : '' }}</textarea>
                @if(old('_modal') === 'event-type-create') @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn ph-btn-primary">Add Event Type</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editEventTypeModal" tabindex="-1" aria-labelledby="editEventTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="editEventTypeForm" action="{{ old('_modal') === 'event-type-edit' ? old('_modal_action') : '' }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="event-type-edit">
            <input type="hidden" name="_modal_action" value="{{ old('_modal') === 'event-type-edit' ? old('_modal_action') : '' }}">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editEventTypeModalLabel">Edit Event Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="editEventTypeName" class="form-label">Event type name <span class="text-danger">*</span></label>
                <input id="editEventTypeName" type="text" name="name" class="form-control ph-input mb-3 {{ old('_modal') === 'event-type-edit' && $errors->has('name') ? 'is-invalid' : '' }}"
                    value="{{ old('_modal') === 'event-type-edit' ? old('name') : '' }}" required maxlength="100">
                @if(old('_modal') === 'event-type-edit') @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                <label for="editEventTypeDescription" class="form-label">Description</label>
                <textarea id="editEventTypeDescription" name="description" rows="3" maxlength="500" class="form-control ph-input mb-3 {{ old('_modal') === 'event-type-edit' && $errors->has('description') ? 'is-invalid' : '' }}">{{ old('_modal') === 'event-type-edit' ? old('description') : '' }}</textarea>
                @if(old('_modal') === 'event-type-edit') @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                <div class="form-check">
                    <input id="editEventTypeActive" type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('_modal') === 'event-type-edit' ? (old('is_active') ? 'checked' : '') : '' }}>
                    <label class="form-check-label" for="editEventTypeActive">This event type is active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn ph-btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteEventTypeModal" tabindex="-1" aria-labelledby="deleteEventTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form method="POST" class="modal-content" id="deleteEventTypeForm">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="deleteEventTypeModalLabel">Confirm deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deleteEventTypeMessage">Are you sure you want to delete this event type?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('[data-bs-target="#editEventTypeModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.getElementById('editEventTypeForm');
            form.action = button.dataset.editAction;
            form.querySelector('[name="_modal_action"]').value = button.dataset.editAction;
            form.querySelector('[name="name"]').value = button.dataset.name;
            form.querySelector('[name="description"]').value = button.dataset.description || '';
            form.querySelector('[name="is_active"]').checked = button.dataset.active === '1';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editEventTypeModal')).show();
        });
    });

    document.querySelectorAll('[data-bs-target="#deleteEventTypeModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('deleteEventTypeForm').action = button.dataset.deleteAction;
            document.getElementById('deleteEventTypeMessage').textContent =
                `Delete event type "${button.dataset.deleteName}"? This action cannot be undone.`;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteEventTypeModal')).show();
        });
    });

    @if($errors->any() && in_array(old('_modal'), ['event-type-create', 'event-type-edit'], true))
        bootstrap.Modal.getOrCreateInstance(document.getElementById(
            old('_modal') === 'event-type-create' ? 'createEventTypeModal' : 'editEventTypeModal'
        )).show();
    @endif
</script>
@endpush
@endsection
