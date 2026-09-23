@extends('layouts.app')

@section('title', 'My Events')

@section('sidebar')
    @include('organizer.partials.sidebar')
@endsection

@section('content')

<div class="organizer-events-header mb-4">
    <div>
        <h2 class="fw-bold mb-1">Events</h2>
        <p class="text-muted mb-0">Manage all of your events in one place.</p>
    </div>

    <a href="{{ route('organizer.events.create') }}" class="btn ph-btn-primary">
        <i class="fas fa-plus me-2"></i>
        Create Event
    </a>
</div>

<div class="organizer-event-filters mb-4" aria-label="Filter events">
    @php
        $selectedFilter = request('status');
    @endphp
    <a href="{{ route('organizer.events.index') }}" class="organizer-event-filter @if(! $selectedFilter) organizer-event-filter--active @endif">All</a>
    <a href="{{ route('organizer.events.index', ['status' => 'upcoming']) }}" class="organizer-event-filter @if($selectedFilter === 'upcoming') organizer-event-filter--active @endif">Upcoming</a>
    <a href="{{ route('organizer.events.index', ['status' => 'ongoing']) }}" class="organizer-event-filter @if($selectedFilter === 'ongoing') organizer-event-filter--active @endif">Ongoing</a>
    <a href="{{ route('organizer.events.index', ['status' => 'ended']) }}" class="organizer-event-filter @if($selectedFilter === 'ended') organizer-event-filter--active @endif">Ended</a>
    <a href="{{ route('organizer.events.index', ['status' => 'completed']) }}" class="organizer-event-filter @if($selectedFilter === 'completed') organizer-event-filter--active @endif">Completed</a>
    <a href="{{ route('organizer.events.index', ['status' => 'cancelled']) }}" class="organizer-event-filter @if($selectedFilter === 'cancelled') organizer-event-filter--active @endif">Cancelled</a>
</div>

@if($events->isEmpty())
    <div class="organizer-events-empty">
        <i class="fas fa-calendar-plus"></i>
        <h5>No events found</h5>
        <p>Create an event or choose a different status filter.</p>
    </div>
@else
    <div class="row g-4 organizer-events-grid">
        @foreach($events as $event)
            <div class="col-lg-6">
                @php
                    $statusClass = 'organizer-event-status--default';

                    if (in_array(strtolower($event->status), ['open', 'upcoming'], true)) {
                        $statusClass = 'organizer-event-status--open';
                    } elseif ($event->status === 'ongoing') {
                        $statusClass = 'organizer-event-status--ongoing';
                    } elseif (strtolower($event->status) === 'completed') {
                        $statusClass = 'organizer-event-status--completed';
                    } elseif (strtolower($event->status) === 'ended') {
                        $statusClass = 'organizer-event-status--ended';
                    } elseif (strtolower($event->status) === 'cancelled') {
                        $statusClass = 'organizer-event-status--cancelled';
                    }
                @endphp

                <article class="ph-card organizer-event-card h-100 overflow-hidden">
                    @if($event->photos->count() > 1)
                        @include('partials.event-photo-collage', ['photos' => $event->photos, 'title' => $event->title])
                    @elseif($event->photos->count() === 1)
                        <div class="organizer-event-cover">
                            @if($event->photos->first()->isVideo())
                                <video controls preload="metadata"><source src="{{ $event->photos->first()->fileUrl() }}"></video>
                            @else
                                <img src="{{ $event->photos->first()->fileUrl() }}" alt="{{ $event->title }}">
                            @endif
                        </div>
                    @elseif($event->coverPhotoUrl())
                        <div class="organizer-event-cover">
                            <img src="{{ $event->coverPhotoUrl() }}" alt="{{ $event->title }}">
                        </div>
                    @else
                        <div class="organizer-event-cover organizer-event-cover--empty">
                            <span class="text-muted">No Event Photo</span>
                        </div>
                    @endif

                    <div class="organizer-event-card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <h5 class="event-card-title mb-0">{{ $event->title }}</h5>
                            <span class="organizer-event-status {{ $statusClass }}">{{ ucfirst($event->status) }}</span>
                        </div>

                        @if($event->description)
                            <p class="organizer-event-description">{{ $event->description }}</p>
                        @endif

                        <div class="organizer-event-meta">
                            <p>
                                <i class="fas fa-calendar"></i>
                                {{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}
                            </p>
                            <p>
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $event->venue }}
                            </p>
                        </div>

                        <div class="organizer-event-actions">
                            <a href="{{ route('organizer.events.show', $event) }}" class="btn ph-btn-primary btn-sm">View Event</a>
                            <a href="{{ route('organizer.events.edit', $event) }}" class="btn ph-btn-outline btn-sm">Edit</a>
                            <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" class="delete-event-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn organizer-event-delete btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
@endif

<!-- Delete Event Modal -->
<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    Delete Event
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <p class="mb-0">
                    Are you sure you want to permanently delete this event?
                </p>

                <small class="text-muted">
                    This action cannot be undone.
                </small>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        id="confirmDeleteBtn"
                        class="btn btn-danger">
                    Delete
                </button>

            </div>

        </div>

    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    let currentForm = null;

    const modal = new bootstrap.Modal(document.getElementById('deleteEventModal'));

    document.querySelectorAll('.delete-event-form').forEach(function(form){

        form.addEventListener('submit', function(e){

            e.preventDefault();
            currentForm = form;
            modal.show();

        });

    });

    document.getElementById('confirmDeleteBtn')
        .addEventListener('click', function(){

            if(currentForm){
                currentForm.submit();
            }

        });

});

</script>

@endsection
