@extends('layouts.app')

@section('title', 'Notifications')

@section('sidebar')
@include('partials.role-sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Notifications</h2>
    <div class="d-flex align-items-center gap-2">
        @if($notifications->contains(fn ($n) => ! $n->is_read))
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn ph-btn-outline btn-sm">Mark all as read</button>
            </form>
        @endif
        @if($notifications->total() > 0)
            <span class="badge bg-primary">{{ $notifications->total() }} total</span>
        @endif
    </div>
</div>

@if($isAdmin)
    <div class="ph-card p-4 mb-4">
        <form method="GET" action="{{ route('notifications.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="notificationTypeFilter">Request type</label>
                <select name="notification_type" id="notificationTypeFilter" class="form-select ph-input">
                    <option value="all" @selected($notificationType === 'all')>All Notifications</option>
                    <option value="category" @selected($notificationType === 'category')>Categories</option>
                    <option value="genre" @selected($notificationType === 'genre')>Genres</option>
                    <option value="event_type" @selected($notificationType === 'event_type')>Event Types</option>
                    <option value="instrument" @selected($notificationType === 'instrument')>Instruments / Specialties</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="notificationRoleFilter">Requester role</label>
                <select name="role" id="notificationRoleFilter" class="form-select ph-input">
                    <option value="all" @selected($role === 'all')>All Roles</option>
                    <option value="performer" @selected($role === 'performer')>Performer</option>
                    <option value="organizer" @selected($role === 'organizer')>Organizer</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
                <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary flex-grow-1">Reset</a>
            </div>
        </form>
    </div>
@endif

@forelse($notifications as $n)
    <form method="POST" action="{{ route('notifications.read', $n) }}" class="mb-2">
        @csrf
        <div class="ph-card p-4 {{ !$n->is_read ? 'border-primary border-2' : '' }}" style="cursor: pointer;">
            <div class="row align-items-start">
                <div class="col">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h6 class="mb-0 fw-bold">{{ $n->title }}</h6>
                        @if(!$n->is_read)
                            <span class="badge bg-primary">New</span>
                        @endif
                    </div>
                    <p class="text-muted mb-2">{{ $n->message }}</p>
                    <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn ph-btn-primary">
                        @if($n->link)
                            <i class="fas fa-arrow-right me-1"></i>View
                        @else
                            <i class="fas fa-check me-1"></i>Mark Read
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </form>
@empty
    <div class="ph-card p-5 text-center">
        <i class="fas fa-bell fa-3x text-muted mb-3"></i>
        <p class="text-muted">No notifications yet.</p>
    </div>
@endforelse

<div class="mt-4">
    {{ $notifications->links() }}
</div>
@endsection
