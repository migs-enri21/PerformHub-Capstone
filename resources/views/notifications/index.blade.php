@extends('layouts.app')

@section('title', 'Notifications')

@section('sidebar')
@include('partials.role-sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Notifications</h2>
@forelse($notifications as $n)
    <div class="ph-card p-3 mb-2 {{ $n->is_read ? '' : 'border-primary' }}">
        <form method="POST" action="{{ route('notifications.read', $n) }}">
            @csrf
            <h6 class="mb-1">{{ $n->title }}</h6>
            <p class="text-muted small mb-2">{{ $n->message }}</p>
            <button class="btn btn-sm ph-btn-outline">View</button>
        </form>
    </div>
@empty
    <p class="text-muted">No notifications.</p>
@endforelse
{{ $notifications->links() }}
@endsection
