@extends('layouts.app')

@section('title', 'New Booking')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Book {{ $performer->stage_name }}</h2>
<form method="POST" action="{{ route('organizer.bookings.store', $performer) }}">
    @csrf
    <div class="ph-card p-4">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label text-muted small">Event Name</label><input type="text" name="event_name" class="form-control ph-input" required></div>
            <div class="col-md-3"><label class="form-label text-muted small">Event Date</label><input type="date" name="event_date" class="form-control ph-input" required></div>
            <div class="col-md-3"><label class="form-label text-muted small">Event Time</label><input type="time" name="event_time" class="form-control ph-input"></div>
            <div class="col-md-6"><label class="form-label text-muted small">Venue</label><input type="text" name="venue" class="form-control ph-input"></div>
            <div class="col-md-3"><label class="form-label text-muted small">Duration (hours)</label><input type="number" name="duration_hours" class="form-control ph-input" min="1" max="24"></div>
            <div class="col-12"><label class="form-label text-muted small">Requirements</label><textarea name="requirements" class="form-control ph-input" rows="3"></textarea></div>
            <div class="col-12"><label class="form-label text-muted small">Notes</label><textarea name="notes" class="form-control ph-input" rows="2"></textarea></div>
        </div>
        <button type="submit" class="btn ph-btn-primary mt-4">Send Booking Request</button>
    </div>
</form>
@endsection
