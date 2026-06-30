@extends('layouts.app')

@section('title', 'Messages')

@section('sidebar')
@include('partials.role-sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Messages</h2>
<div class="ph-card p-4">
    @forelse($conversations as $userId => $msgs)
        @php $other = $msgs->first()->sender_id === auth()->id() ? $msgs->first()->receiver : $msgs->first()->sender; @endphp
        <a href="{{ route('messages.show', $other) }}" class="d-flex align-items-center gap-3 p-3 rounded text-decoration-none text-white mb-2" style="background:var(--ph-bg-input);">
            <i class="fas fa-user-circle fa-2x text-muted"></i>
            <div><h6 class="mb-0">{{ $other->name }}</h6><small class="text-muted">{{ Str::limit($msgs->first()->message, 50) }}</small></div>
        </a>
    @empty
        <p class="text-muted mb-0">No conversations yet.</p>
    @endforelse
</div>
@endsection
