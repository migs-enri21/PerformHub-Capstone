@extends('layouts.guest')

@section('title', 'Session Expired')

@section('content')
<div class="d-flex align-items-center justify-content-center min-vh-100 p-4">
    <div class="text-center" style="max-width: 420px;">
        <h1 class="fw-bold mb-2">Session expired</h1>
        <p class="text-muted mb-4">
            This page’s security token is out of date. Don’t refresh this screen — open Sign In instead so the browser loads a new token.
        </p>
        <a href="{{ url('/login') }}" class="btn ph-btn-primary">Go to Sign In</a>
    </div>
</div>
@endsection
