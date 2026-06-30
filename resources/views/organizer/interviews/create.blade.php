@extends('layouts.app')

@section('title', 'Schedule Interview')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Schedule Interview — {{ $booking->event_name }}</h2>
<form method="POST" action="{{ route('organizer.interviews.store', $booking) }}">
    @csrf
    <div class="ph-card p-4" style="max-width:500px;">
        <div class="mb-3"><label class="form-label text-muted small">Date & Time</label><input type="datetime-local" name="scheduled_at" class="form-control ph-input" required></div>
        <div class="mb-3"><label class="form-label text-muted small">Notes</label><textarea name="notes" class="form-control ph-input" rows="3"></textarea></div>
        <button type="submit" class="btn ph-btn-primary">Schedule via Jitsi Meet</button>
    </div>
</form>
@endsection
