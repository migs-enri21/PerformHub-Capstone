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
            <div class="mb-4">

    <label class="form-label">Select Existing Event</label>

    <select id="eventSelector"class="form-select ph-input">

        <option value=""> -- Select an Event --</option>

        @foreach($events as $event)

            <option
                value="{{ $event->id }}"
                data-title="{{ $event->title }}"
                data-date="{{ $event->event_date }}"
                data-time="{{ $event->start_time }}"
                data-venue="{{ $event->venue }}"
                data-requirements="{{ $event->requirements }}">

                {{ $event->title }}

            </option>
            @endforeach

            </select></div>

            <div class="col-md-6"><label class="form-label text-muted small">Event Name</label><input type="text" name="event_name"class="form-control ph-input" id="event_name" value="{{ old('event_name') }}"required></div>
            <div class="col-md-3"><label class="form-label text-muted small">Event Date</label><input type="date" name="event_date"class="form-control ph-input" id="event_date" value="{{ old('event_date') }}"required></div>
            <div class="col-md-3"><label class="form-label text-muted small">Event Time</label><input type="time" name="event_time" class="form-control ph-input"id="event_time" value="{{ old('event_time') }}"required></div>
            <div class="col-md-6"><label class="form-label text-muted small">Venue</label><input type="text" name="venue" class="form-control ph-input" id="venue" value="{{ old('venue') }}"required></div>
            <div class="col-md-3"><label class="form-label text-muted small">Duration (hours)</label><input type="number" name="duration_hours" class="form-control ph-input" min="1" max="24"></div>
            <div class="col-12"><label class="form-label text-muted small">Requirements</label><textarea id="requirements" name="requirements" class="form-control ph-input">{{ old('requirements') }}</textarea></div>
            <div class="col-12"><label class="form-label text-muted small">Notes</label><textarea name="notes" class="form-control ph-input" rows="2"></textarea></div>

        </div>
        <button type="submit" class="btn ph-btn-primary mt-4">Send Booking Request</button>
    </div>
</form>

    <script>
    document.addEventListener('DOMContentLoaded', () => {

    const selector = document.getElementById('eventSelector');

    selector.addEventListener('change', function () {

        const selected = this.options[this.selectedIndex];

        document.getElementById('event_name').value = selected.dataset.title || '';
        document.getElementById('event_date').value = selected.dataset.date || '';
        document.getElementById('event_time').value = selected.dataset.time || '';
        document.getElementById('venue').value = selected.dataset.venue || '';
        document.getElementById('requirements').value = selected.dataset.requirements || '';
        });

    });
    </script>

@endsection
