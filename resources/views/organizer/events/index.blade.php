@extends('layouts.app')

@section('title', 'My Events')

@section('sidebar')
    @include('organizer.partials.sidebar')
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Events</h2>
        <p class="text-muted mb-0">Manage all of your events in one place.</p>
    </div>

    <a href="{{ route('organizer.events.create') }}" class="btn ph-btn-primary">
        <i class="fas fa-plus me-2"></i>
        Create Event
    </a>
</div>

<div class="mb-4">
    <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-primary btn-sm me-2">All</a>
    <a href="{{ route('organizer.events.index', ['status' => 'upcoming']) }}" class="btn btn-outline-secondary btn-sm me-2">Upcoming</a>
    <a href="{{ route('organizer.events.index', ['status' => 'ongoing']) }}" class="btn btn-outline-secondary btn-sm me-2">Ongoing</a>
    <a href="{{ route('organizer.events.index', ['status' => 'completed']) }}" class="btn btn-outline-secondary btn-sm me-2">Completed</a>
    <a href="{{ route('organizer.events.index', ['status' => 'cancelled']) }}" class="btn btn-outline-secondary btn-sm">Cancelled</a>
</div>

@if($events->isEmpty())
    <div class="alert alert-info">You haven't created any events yet.</div>
@else
    <div class="row g-4">
        @foreach($events as $event)
            <div class="col-lg-6">
                <div class="ph-card organizer-event-card h-100 overflow-hidden">
                    @if($event->photos->count() > 1)
                        @include('partials.event-photo-collage', ['photos' => $event->photos, 'title' => $event->title])
                    @elseif($event->photos->count() === 1)
                        <div class="organizer-event-cover">
                            <img src="{{ $event->photos->first()->fileUrl() }}" alt="{{ $event->title }}">
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

                    <div class="p-3">
                        <h5 class="fw-bold text-white mb-2">{{ $event->title }}</h5>

                        @if($event->description)
                            <p class="text-muted small mb-3">{{ $event->description }}</p>
                        @endif

                        <p class="text-muted mb-2 small">
                            <i class="fas fa-calendar me-2"></i>
                            {{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}
                        </p>

                        <p class="text-muted mb-3 small">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            {{ $event->venue }}
                        </p>

                        @if(in_array(strtolower($event->status), ['open', 'upcoming'], true))
                            <span class="badge bg-primary">
                        @elseif($event->status == 'ongoing')
                            <span class="badge bg-success">
                        @elseif(in_array(strtolower($event->status), ['completed'], true))
                            <span class="badge bg-dark">
                        @elseif(in_array(strtolower($event->status), ['cancelled'], true))
                            <span class="badge bg-danger">
                        @else
                            <span class="badge bg-secondary">
                        @endif
                            {{ ucfirst($event->status) }}
                        </span>

                        <div class="mt-3 d-flex flex-wrap gap-2">
                            <a href="{{ route('organizer.events.show', $event) }}" class="btn btn-outline-primary btn-sm">View</a>
                            <a href="{{ route('organizer.events.edit', $event) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" onsubmit="return confirm('Delete this event permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
