@extends('layouts.app')

@section('title', 'Manage Users')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">User Management</h2>
<div class="ph-card p-4 mb-4">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><select name="role" class="form-select ph-input"><option value="">All Roles</option><option value="performer" @selected(request('role')=='performer')>Performer</option><option value="organizer" @selected(request('role')=='organizer')>Organizer</option></select></div>
        <div class="col-md-3"><select name="status" class="form-select ph-input"><option value="">All Status</option><option value="active" @selected(request('status')=='active')>Active</option><option value="inactive" @selected(request('status')=='inactive')>Inactive</option></select></div>
        <div class="col-md-2"><button class="btn ph-btn-primary">Filter</button></div>
    </form>
</div>
<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead><tr><th>Name</th><th>Role</th><th>Verified</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->first_name }} {{ $user->last_name }} <small class="text-muted">@{{ $user->username }}</small></td>
                    <td><span class="badge bg-secondary">{{ ucfirst($user->role) }}</span></td>
                    <td>@if($user->is_verified)<span class="text-success">Yes</span>@else<span class="text-warning">No</span>@endif</td>
                    <td>@if($user->is_active)<span class="text-success">Active</span>@else<span class="text-danger">Suspended</span>@endif</td>
                    <td class="d-flex gap-1">
                        @if(!$user->is_verified)<form method="POST" action="{{ route('admin.users.verify', $user) }}">@csrf<button class="btn btn-sm btn-success">Verify</button></form>@endif
                        <form method="POST" action="{{ route('admin.users.toggle', $user) }}">@csrf<button class="btn btn-sm btn-outline-warning">{{ $user->is_active ? 'Suspend' : 'Activate' }}</button></form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $users->links() }}
@endsection
