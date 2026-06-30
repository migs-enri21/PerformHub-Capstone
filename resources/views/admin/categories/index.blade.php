@extends('layouts.app')

@section('title', 'Categories')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Category Management</h2>
<div class="ph-card p-4 mb-4">
    <form method="POST" action="{{ route('admin.categories.store') }}" class="row g-2">
        @csrf
        <div class="col-md-3"><input type="text" name="name" class="form-control ph-input" placeholder="Category name" required></div>
        <div class="col-md-3"><input type="text" name="icon" class="form-control ph-input" placeholder="fa-music"></div>
        <div class="col-md-4"><input type="text" name="description" class="form-control ph-input" placeholder="Description"></div>
        <div class="col-md-2"><button class="btn ph-btn-primary w-100">Add</button></div>
    </form>
</div>
<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead><tr><th>Name</th><th>Icon</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat->name }}</td>
                    <td><i class="fas {{ $cat->icon ?? 'fa-star' }}"></i></td>
                    <td><span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $categories->links() }}
@endsection
