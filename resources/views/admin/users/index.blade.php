@extends('layouts.app')

@section('title', 'Manage Users')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .4rem .7rem;
        border: 1px solid transparent;
        font-weight: 600;
    }

    .role-badge--performer {
        color: #5b21b6;
        background: #ede9fe;
        border-color: #ddd6fe;
    }

    .role-badge--organizer {
        color: #0f766e;
        background: #ccfbf1;
        border-color: #99f6e4;
    }

    .role-badge--admin {
        color: #b91c1c;
        background: #fee2e2;
        border-color: #fecaca;
    }

    .role-badge--default {
        color: #475569;
        background: #f1f5f9;
        border-color: #e2e8f0;
    }
</style>
@endpush

@section('content')
<h2 class="fw-bold mb-4">User Management</h2>
<div class="ph-card p-4 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <select name="role" class="form-select ph-input">
                <option value="">All Roles</option>
                <option value="performer" @selected(request('role') === 'performer')>Performer</option>
                <option value="organizer" @selected(request('role') === 'organizer')>Organizer</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select ph-input">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="verification" class="form-select ph-input">
                <option value="">All Verification Status</option>
                <option value="pending" @selected(request('verification') === 'pending')>Pending Verification</option>
            </select>
        </div>
        @include('admin.partials.sort-filter', ['sortByLabel' => 'Name', 'defaultSort' => 'latest'])
        <div class="col-md-1"><button class="btn ph-btn-primary w-100">Filter</button></div>
    </form>
</div>
<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead><tr><th>Name</th><th>Role</th><th class="text-center">Verified</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ Str::title($user->first_name) }} {{ Str::title($user->last_name) }}</td>
                    @php
                        $roleBadgeClass = match ($user->role) {
                            'performer' => 'role-badge--performer',
                            'organizer' => 'role-badge--organizer',
                            'admin' => 'role-badge--admin',
                            default => 'role-badge--default',
                        };
                        $roleIconClass = match ($user->role) {
                            'performer' => 'fa-music',
                            'organizer' => 'fa-building',
                            'admin' => 'fa-shield-halved',
                            default => 'fa-user',
                        };
                    @endphp
                    <td>
                        <span class="badge rounded-pill role-badge {{ $roleBadgeClass }}">
                            <i class="fas {{ $roleIconClass }}" aria-hidden="true"></i>
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="text-center">@if($user->is_verified)<span class="badge bg-primary">Yes</span>@else<span class="badge bg-warning text-dark">No</span>@endif</td>
                    <td class="text-center">@if($user->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-danger">Suspended</span>@endif</td>
                    <td class="text-center">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info">View</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $users->links() }}
@endsection
