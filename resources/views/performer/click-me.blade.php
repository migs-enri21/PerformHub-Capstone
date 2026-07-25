@extends('layouts.app')

@section('title', 'Click Me')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
    <h2 class="fw-bold mb-4">Click Me Dashboard</h2>

    <div class="ph-card p-4">
        <p class="mb-0">This is my new dashboard page.</p>
    </div>
@endsection