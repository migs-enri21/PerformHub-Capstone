@extends('layouts.app')

@section('title', 'Notifications')

@section('sidebar')
@include('partials.role-sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Notifications</h2>
    @if($notifications->total() > 0)
        <span class="badge bg-primary">{{ $notifications->total() }} total</span>
    @endif
</div>

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
