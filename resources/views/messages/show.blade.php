@extends('layouts.app')

@section('title', 'Chat with '.$user->name)

@section('content')
<h2 class="fw-bold mb-4">{{ $user->name }}</h2>
<div class="ph-card p-4 mb-3" style="max-height:400px;overflow-y:auto;">
    @foreach($messages as $msg)
        <div class="mb-3 {{ $msg->sender_id === auth()->id() ? 'text-end' : '' }}">
            <div class="d-inline-block p-2 rounded {{ $msg->sender_id === auth()->id() ? 'bg-primary' : '' }}" style="{{ $msg->sender_id !== auth()->id() ? 'background:var(--ph-bg-input);' : '' }}">
                <small class="d-block opacity-75">{{ $msg->sender->name }}</small>
                {{ $msg->message }}
            </div>
        </div>
    @endforeach
</div>
<form method="POST" action="{{ route('messages.store', $user) }}">
    @csrf
    <div class="input-group">
        <input type="text" name="message" class="form-control ph-input" placeholder="Type a message..." required>
        <button class="btn ph-btn-primary">Send</button>
    </div>
</form>
@endsection
