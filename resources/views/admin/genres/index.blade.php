@extends('layouts.app')

@section('title', 'Genre & Specialty Management')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Genre & Specialty Management</h2>
</div>

<div class="ph-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.genres.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control ph-input" placeholder="Search by name or description" value="{{ $search ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select ph-input">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(($categoryId ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select ph-input">
                <option value="">All Status</option>
                <option value="active" {{ ($status === 'active') ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ ($status === 'inactive') ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('admin.genres.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
        </div>
    </form>
</div>

<div class="ph-card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="fw-bold mb-1">Genres</h5>
            <p class="text-muted mb-0">Manage genres and their performer categories.</p>
        </div>
        <button type="button" class="btn ph-btn-primary" data-bs-toggle="modal" data-bs-target="#createGenreModal">
            <i class="fas fa-plus me-1"></i> Add Genre
        </button>
    </div>
</div>

@include('admin.partials.named-option-table', [
    'heading' => 'Genres',
    'items' => $genres,
    'prefix' => 'genres',
    'itemLabel' => 'genre',
    'empty' => 'No genres found',
    'showCategory' => true,
    'extraClass' => 'mb-4',
])

<div class="ph-card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h5 class="fw-bold mb-1">Specialties</h5>
            <p class="text-muted mb-0">Manage performer specialties.</p>
        </div>
        <button type="button" class="btn ph-btn-primary" data-bs-toggle="modal" data-bs-target="#createSpecialtyModal">
            <i class="fas fa-plus me-1"></i> Add Specialty
        </button>
    </div>
</div>

@include('admin.partials.named-option-table', [
    'heading' => 'Specialties',
    'items' => $specialties,
    'prefix' => 'specialties',
    'itemLabel' => 'specialty',
    'empty' => 'No specialties found',
])

<div class="modal fade" id="createGenreModal" tabindex="-1" aria-labelledby="createGenreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.genres.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="_modal" value="genre-create">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="createGenreModalLabel">Add Genre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="newGenreName" class="form-label">Genre name <span class="text-danger">*</span></label>
                <input id="newGenreName" type="text" name="name" maxlength="100" required
                    class="form-control ph-input mb-3 {{ old('_modal') === 'genre-create' && $errors->has('name') ? 'is-invalid' : '' }}"
                    value="{{ old('_modal') === 'genre-create' ? old('name') : '' }}">
                @if(old('_modal') === 'genre-create') @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                <label for="newGenreCategory" class="form-label">Category <span class="text-danger">*</span></label>
                <select id="newGenreCategory" name="category_id" required
                    class="form-select ph-input mb-3 {{ old('_modal') === 'genre-create' && $errors->has('category_id') ? 'is-invalid' : '' }}">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('_modal') === 'genre-create' && old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @if(old('_modal') === 'genre-create') @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror @endif
                <label for="newGenreDescription" class="form-label">Description</label>
                <textarea id="newGenreDescription" name="description" maxlength="500" rows="3"
                    class="form-control ph-input {{ old('_modal') === 'genre-create' && $errors->has('description') ? 'is-invalid' : '' }}">{{ old('_modal') === 'genre-create' ? old('description') : '' }}</textarea>
                @if(old('_modal') === 'genre-create') @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn ph-btn-primary">Add Genre</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="createSpecialtyModal" tabindex="-1" aria-labelledby="createSpecialtyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.specialties.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="_modal" value="specialty-create">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="createSpecialtyModalLabel">Add Specialty</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="newSpecialtyName" class="form-label">Specialty name <span class="text-danger">*</span></label>
                <input id="newSpecialtyName" type="text" name="name" maxlength="100" required
                    class="form-control ph-input mb-3 {{ old('_modal') === 'specialty-create' && $errors->has('name') ? 'is-invalid' : '' }}"
                    value="{{ old('_modal') === 'specialty-create' ? old('name') : '' }}">
                @if(old('_modal') === 'specialty-create') @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                <label for="newSpecialtyDescription" class="form-label">Description</label>
                <textarea id="newSpecialtyDescription" name="description" maxlength="500" rows="3"
                    class="form-control ph-input {{ old('_modal') === 'specialty-create' && $errors->has('description') ? 'is-invalid' : '' }}">{{ old('_modal') === 'specialty-create' ? old('description') : '' }}</textarea>
                @if(old('_modal') === 'specialty-create') @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn ph-btn-primary">Add Specialty</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editNamedOptionModal" tabindex="-1" aria-labelledby="editNamedOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="editNamedOptionForm"
            @if(in_array(old('_modal'), ['genre-edit', 'specialty-edit'], true)) action="{{ old('_modal_action') }}" @endif>
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="{{ old('_modal') === 'genre-edit' ? 'genre-edit' : 'specialty-edit' }}">
            <input type="hidden" name="_modal_action" value="{{ in_array(old('_modal'), ['genre-edit', 'specialty-edit'], true) ? old('_modal_action') : '' }}">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="editNamedOptionModalLabel">Edit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="editNamedOptionName" class="form-label">Name <span class="text-danger">*</span></label>
                <input id="editNamedOptionName" type="text" name="name" maxlength="100" required
                    class="form-control ph-input mb-3 {{ in_array(old('_modal'), ['genre-edit', 'specialty-edit'], true) && $errors->has('name') ? 'is-invalid' : '' }}"
                    value="{{ in_array(old('_modal'), ['genre-edit', 'specialty-edit'], true) ? old('name') : '' }}">
                @if(in_array(old('_modal'), ['genre-edit', 'specialty-edit'], true)) @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                <div id="editNamedOptionCategoryGroup" class="mb-3">
                    <label for="editNamedOptionCategory" class="form-label">Category <span class="text-danger">*</span></label>
                    <select id="editNamedOptionCategory" name="category_id" class="form-select ph-input">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('_modal') === 'genre-edit' && old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @if(old('_modal') === 'genre-edit') @error('category_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror @endif
                </div>
                <label for="editNamedOptionDescription" class="form-label">Description</label>
                <textarea id="editNamedOptionDescription" name="description" maxlength="500" rows="3"
                    class="form-control ph-input mb-3 {{ in_array(old('_modal'), ['genre-edit', 'specialty-edit'], true) && $errors->has('description') ? 'is-invalid' : '' }}">{{ in_array(old('_modal'), ['genre-edit', 'specialty-edit'], true) ? old('description') : '' }}</textarea>
                @if(in_array(old('_modal'), ['genre-edit', 'specialty-edit'], true)) @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror @endif
                <div class="form-check">
                    <input id="editNamedOptionActive" type="checkbox" name="is_active" value="1" class="form-check-input"
                        {{ in_array(old('_modal'), ['genre-edit', 'specialty-edit'], true) ? (old('is_active') ? 'checked' : '') : '' }}>
                    <label class="form-check-label" for="editNamedOptionActive">This item is active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn ph-btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteNamedOptionModal" tabindex="-1" aria-labelledby="deleteNamedOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form method="POST" class="modal-content" id="deleteNamedOptionForm">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="deleteNamedOptionModalLabel">Confirm deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deleteNamedOptionMessage">Are you sure you want to delete this item?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('[data-bs-target="#editNamedOptionModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.getElementById('editNamedOptionForm');
            const isGenre = button.dataset.itemType === 'genre';
            const categoryGroup = document.getElementById('editNamedOptionCategoryGroup');
            form.action = button.dataset.editAction;
            form.querySelector('[name="_modal_action"]').value = button.dataset.editAction;
            form.querySelector('[name="_modal"]').value = isGenre ? 'genre-edit' : 'specialty-edit';
            form.querySelector('[name="name"]').value = button.dataset.name;
            form.querySelector('[name="description"]').value = button.dataset.description || '';
            form.querySelector('[name="is_active"]').checked = button.dataset.active === '1';
            categoryGroup.hidden = !isGenre;
            form.querySelector('[name="category_id"]').required = isGenre;
            form.querySelector('[name="category_id"]').value = button.dataset.categoryId || '';
            document.getElementById('editNamedOptionModalLabel').textContent = `Edit ${isGenre ? 'Genre' : 'Specialty'}`;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editNamedOptionModal')).show();
        });
    });

    document.querySelectorAll('[data-bs-target="#deleteNamedOptionModal"]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('deleteNamedOptionForm').action = button.dataset.deleteAction;
            document.getElementById('deleteNamedOptionMessage').textContent =
                `Delete ${button.dataset.itemType} "${button.dataset.name}"? This action cannot be undone.`;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteNamedOptionModal')).show();
        });
    });

    @if($errors->any() && in_array(old('_modal'), ['genre-create', 'specialty-create', 'genre-edit', 'specialty-edit'], true))
        @if(in_array(old('_modal'), ['genre-edit', 'specialty-edit'], true))
            document.getElementById('editNamedOptionCategoryGroup').hidden = @json(old('_modal') === 'specialty-edit');
            document.getElementById('editNamedOptionCategory').required = @json(old('_modal') === 'genre-edit');
            document.getElementById('editNamedOptionModalLabel').textContent = @json(old('_modal') === 'genre-edit' ? 'Edit Genre' : 'Edit Specialty');
        @endif
        bootstrap.Modal.getOrCreateInstance(document.getElementById({
            'genre-create': 'createGenreModal',
            'specialty-create': 'createSpecialtyModal',
            'genre-edit': 'editNamedOptionModal',
            'specialty-edit': 'editNamedOptionModal'
        }[@json(old('_modal'))]).show();
    @endif
</script>
@endpush
@endsection
