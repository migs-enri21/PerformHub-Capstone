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
    <div class="col-12">
        <div class="ph-card p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-bold mb-1">Categories</h5>
                <p class="text-muted mb-0">Manage performer categories.</p>
            </div>
            <button type="button" class="btn ph-btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="fas fa-plus me-1"></i> Add Category
            </button>
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
                        <td class="align-middle"><p class="mb-0 text-truncate admin-description-truncate">{{ $cat->description ?? '—' }}</p></td>
                        <td class="align-middle"><span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="align-middle text-center"><span class="badge bg-info">{{ $cat->performers->count() }}</span></td>
                        <td class="align-middle text-end">
                            <a href="{{ route('admin.categories.show', $cat) }}" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                            <button type="button" class="btn btn-sm btn-outline-warning" title="Edit"
                                data-bs-target="#editCategoryModal"
                                data-edit-action="{{ route('admin.categories.update', $cat) }}"
                                data-name="{{ $cat->name }}" data-description="{{ $cat->description }}"
                                data-active="{{ $cat->is_active ? '1' : '0' }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.categories.toggle', $cat) }}" class="admin-action-form">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $cat->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $cat->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas {{ $cat->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                data-bs-target="#deleteCategoryItemModal"
                                data-delete-action="{{ route('admin.categories.destroy', $cat) }}"
                                data-delete-name="{{ $cat->name }}" data-delete-type="category">
                                <i class="fas fa-trash"></i>
                            </button>
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

<div class="ph-card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="fw-bold mb-1">Event Types</h5>
            <p class="text-muted mb-0">Manage available event types.</p>
        </div>
        <button type="button" class="btn ph-btn-primary" data-bs-toggle="modal" data-bs-target="#createEventTypeModal">
            <i class="fas fa-plus me-1"></i> Add Event Type
        </button>
    </div>
</div>

<div class="ph-card p-4">
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
                            <button type="button" class="btn btn-sm btn-outline-warning" title="Edit"
                                data-bs-target="#editEventTypeModal"
                                data-edit-action="{{ route('admin.event-types.update', $eventType) }}"
                                data-name="{{ $eventType->name }}" data-description="{{ $eventType->description }}"
                                data-active="{{ $eventType->is_active ? '1' : '0' }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.event-types.toggle', $eventType) }}" class="admin-action-form">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $eventType->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $eventType->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas {{ $eventType->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                data-bs-target="#deleteCategoryItemModal"
                                data-delete-action="{{ route('admin.event-types.destroy', $eventType) }}"
                                data-delete-name="{{ $eventType->name }}" data-delete-type="event type">
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
        {{ $eventTypes->links('pagination::bootstrap-5') }}
    </div>
</div>

<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="_modal" value="category-create">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="createCategoryModalLabel">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="newCategoryName" class="form-label">Category name <span class="text-danger">*</span></label>
                <input id="newCategoryName" type="text" name="name" class="form-control ph-input mb-3 {{ old('_modal') === 'category-create' && $errors->has('name') ? 'is-invalid' : '' }}"
                    value="{{ old('_modal') === 'category-create' ? old('name') : '' }}" required maxlength="100">
                @if(old('_modal') === 'category-create') @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                <label for="newCategoryDescription" class="form-label">Description</label>
                <textarea id="newCategoryDescription" name="description" rows="3" maxlength="500" class="form-control ph-input {{ old('_modal') === 'category-create' && $errors->has('description') ? 'is-invalid' : '' }}">{{ old('_modal') === 'category-create' ? old('description') : '' }}</textarea>
                @if(old('_modal') === 'category-create') @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn ph-btn-primary">Add Category</button>
            </div>
        </form>
    </div>
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

<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="editCategoryForm" action="{{ old('_modal') === 'category-edit' ? old('_modal_action') : '' }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="category-edit">
            <input type="hidden" name="_modal_action" value="{{ old('_modal') === 'category-edit' ? old('_modal_action') : '' }}">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="editCategoryName" class="form-label">Category name <span class="text-danger">*</span></label>
                <input id="editCategoryName" type="text" name="name" class="form-control ph-input mb-3 {{ old('_modal') === 'category-edit' && $errors->has('name') ? 'is-invalid' : '' }}"
                    value="{{ old('_modal') === 'category-edit' ? old('name') : '' }}" required maxlength="100">
                @if(old('_modal') === 'category-edit') @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                <label for="editCategoryDescription" class="form-label">Description</label>
                <textarea id="editCategoryDescription" name="description" rows="3" maxlength="500" class="form-control ph-input mb-3 {{ old('_modal') === 'category-edit' && $errors->has('description') ? 'is-invalid' : '' }}">{{ old('_modal') === 'category-edit' ? old('description') : '' }}</textarea>
                @if(old('_modal') === 'category-edit') @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                <div class="form-check">
                    <input id="editCategoryActive" type="checkbox" name="is_active" value="1" class="form-check-input" {{ old('_modal') === 'category-edit' ? (old('is_active') ? 'checked' : '') : '' }}>
                    <label class="form-check-label" for="editCategoryActive">This category is active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn ph-btn-primary">Save Changes</button>
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

<div class="modal fade" id="deleteCategoryItemModal" tabindex="-1" aria-labelledby="deleteCategoryItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form method="POST" class="modal-content" id="deleteCategoryItemForm">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="deleteCategoryItemModalLabel">Confirm deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deleteCategoryItemMessage">Are you sure you want to delete this item?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('[data-bs-target="#editCategoryModal"], [data-bs-target="#editEventTypeModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            const isCategory = button.dataset.bsTarget === '#editCategoryModal';
            const modalId = isCategory ? 'editCategoryModal' : 'editEventTypeModal';
            const form = document.getElementById(isCategory ? 'editCategoryForm' : 'editEventTypeForm');
            form.action = button.dataset.editAction;
            form.querySelector('[name="_modal_action"]').value = button.dataset.editAction;
            form.querySelector('[name="name"]').value = button.dataset.name;
            form.querySelector('[name="description"]').value = button.dataset.description || '';
            form.querySelector('[name="is_active"]').checked = button.dataset.active === '1';
            bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId)).show();
        });
    });

    document.querySelectorAll('[data-bs-target="#deleteCategoryItemModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('deleteCategoryItemForm').action = button.dataset.deleteAction;
            document.getElementById('deleteCategoryItemMessage').textContent =
                `Delete ${button.dataset.deleteType} "${button.dataset.deleteName}"? This action cannot be undone.`;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteCategoryItemModal')).show();
        });
    });

    @if($errors->any() && in_array(old('_modal'), ['category-create', 'event-type-create', 'category-edit', 'event-type-edit'], true))
        bootstrap.Modal.getOrCreateInstance(document.getElementById({
            'category-create': 'createCategoryModal',
            'event-type-create': 'createEventTypeModal',
            'category-edit': 'editCategoryModal',
            'event-type-edit': 'editEventTypeModal'
        }[@json(old('_modal'))]).show();
    @endif
</script>
@endpush


@if(session('created_category'))
    @php($createdCategory = session('created_category'))
    <div class="modal fade" id="createGenreModal" tabindex="-1" aria-labelledby="createGenreModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form method="POST" action="{{ route('admin.genres.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="category_id" value="{{ $createdCategory['id'] }}">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="createGenreModalLabel">Add a genre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Add a genre under <strong>{{ $createdCategory['name'] }}</strong>.
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="This category is already selected for the new genre.">
                            <i class="fas fa-circle-info" aria-hidden="true"></i>
                            <span class="visually-hidden">Why is this category selected?</span>
                        </button>
                    </p>
                    <input type="text" name="name" class="form-control ph-input mb-2" placeholder="Genre name" required autocomplete="off">
                    <input type="text" name="description" class="form-control ph-input" placeholder="Description">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Skip</button>
                    <button type="submit" class="btn ph-btn-primary">Add Genre</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const createGenreModalElement = document.getElementById('createGenreModal');

        if (createGenreModalElement && window.bootstrap) {
            const createGenreModal = new bootstrap.Modal(createGenreModalElement);
            const tooltipTrigger = createGenreModalElement.querySelector('[data-bs-toggle="tooltip"]');
            const genreForm = createGenreModalElement.querySelector('form');
            let modalWasTouched = false;

            if (tooltipTrigger) {
                new bootstrap.Tooltip(tooltipTrigger);
            }

            genreForm.addEventListener('focusin', () => {
                modalWasTouched = true;
            });
            genreForm.addEventListener('input', () => {
                modalWasTouched = true;
            });
            genreForm.addEventListener('submit', () => {
                modalWasTouched = true;
            });

            createGenreModal.show();

            setTimeout(() => {
                if (!modalWasTouched) {
                    createGenreModal.hide();
                }
            }, 6000);
        }
    </script>
    @endpush
@endif
@endsection
