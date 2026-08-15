@extends('layouts.app')

@section('title', 'New Booking')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@php
    $eventDetails = [
        'id' => null,
        'title' => null,
        'date' => null,
        'start_time' => null,
        'end_time' => null,
        'venue' => null,
        'budget' => null,
        'description' => null,
    ];

    if ($selectedEvent) {
        $eventDetails = [
            'id' => $selectedEvent->id,
            'title' => $selectedEvent->title,
            'date' => $selectedEvent->event_date,
            'start_time' => $selectedEvent->start_time,
            'end_time' => $selectedEvent->end_time,
            'venue' => $selectedEvent->venue,
            'budget' => $selectedEvent->budget,
            'description' => $selectedEvent->description,
        ];
    }

    $selectedEventBudget = $eventDetails['budget'];
@endphp

<h2 class="fw-bold mb-4">Book {{ $performer->stage_name }}</h2>
<form method="POST" action="{{ route('organizer.bookings.store', $performer) }}">
    @csrf
    <div class="ph-card p-4">
        <div class="row g-3">
            <div class="mb-4">

    <label class="form-label">Select Event to Book</label>

    <select id="eventSelector" class="form-select ph-input">

        <option value=""> -- Select an Event --</option>

        @foreach($events as $event)

            <option
                value="{{ $event->id }}"
                data-id="{{ $event->id }}"
                data-title="{{ $event->title }}"
                data-date="{{ $event->event_date }}"
                data-start="{{ $event->start_time }}"
                data-end="{{ $event->end_time }}"
                data-venue="{{ $event->venue }}"
                data-description="{{ $event->description }}"
                data-budget="{{ $event->budget }}"
                @selected($eventDetails['id'] == $event->id)>

                {{ $event->title }}

            </option>
        @endforeach

        <input type="hidden" name="event_id" id="event_id" value="{{ $eventDetails['id'] }}">

            </select></div>

            <div class="col-md-6"><label class="form-label text-muted small">Event Name</label><input type="text" name="event_name"class="form-control ph-input" id="event_name" value="{{ old('event_name', $eventDetails['title']) }}"required></div>
            <div class="col-md-3"><label class="form-label text-muted small">Event Date</label><input type="date" name="event_date"class="form-control ph-input" id="event_date" value="{{ old('event_date', $eventDetails['date']) }}"required></div>
            <div class="col-md-3"><label class="form-label text-muted small">Event Time</label><input type="time" name="event_time" class="form-control ph-input"id="event_time" value="{{ old('event_time', $eventDetails['start_time']) }}"required></div>
            <div class="col-md-6"><label class="form-label text-muted small">Venue</label><input type="text" name="venue" class="form-control ph-input" id="venue" value="{{ old('venue', $eventDetails['venue']) }}"required></div>
            <div class="col-md-4"><label class="form-label text-muted small">Budget Offer (₱)</label><input type="number" name="budget" id="budget" class="form-control ph-input" value="{{ old('budget', $selectedEventBudget) }}" min="0" step="0.01"required></div>
            <div class="col-md-3"><label class="form-label text-muted small">End Time</label><input type="time" name="end_time" id="end_time" class="form-control ph-input" value="{{ old('end_time', $eventDetails['end_time']) }}"required></div>
            <div class="col-12"><label class="form-label text-muted small">Requirements</label><textarea id="requirements" name="requirements" class="form-control ph-input">{{ old('requirements', $eventDetails['description']) }}</textarea></div>
            <div class="col-12"><label class="form-label text-muted small">Notes</label><textarea name="notes" class="form-control ph-input" rows="2"></textarea></div>

        </div>
        <button type="submit" class="btn ph-btn-primary mt-4">Send Booking Request</button>
    </div>
</form>

    <script>
    document.addEventListener('DOMContentLoaded', () => {

    const selector = document.getElementById('eventSelector');
    const eventTimeInput = document.getElementById('event_time');
    const endTimeInput = document.getElementById('end_time');

    function removeSeconds(time) {
        if (time) {
            return time.substring(0, 5);
        }

        return '';
    }

    eventTimeInput.value = removeSeconds(eventTimeInput.value);
    endTimeInput.value = removeSeconds(endTimeInput.value);

    selector.addEventListener('change', function () {

            const selected = this.options[this.selectedIndex];

            document.getElementById('event_name').value = selected.dataset.title || '';
            document.getElementById('event_id').value = selected.dataset.id || '';
            document.getElementById('event_date').value = selected.dataset.date || '';
            eventTimeInput.value = removeSeconds(selected.dataset.start);
            endTimeInput.value = removeSeconds(selected.dataset.end);
            document.getElementById('venue').value = selected.dataset.venue || '';
            document.getElementById('budget').value = selected.dataset.budget || '';
            document.getElementById('requirements').value = selected.dataset.description || '';;
        });

    });
    </script>

@endsection
