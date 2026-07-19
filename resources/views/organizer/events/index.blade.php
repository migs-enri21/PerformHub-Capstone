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

    <a href="{{ route('organizer.events.create') }}"
       class="btn ph-btn-primary">

        <i class="fas fa-plus me-2"></i>
        Create Event
    </a>

</div>

<div class="mb-4">

    <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-primary btn-sm me-2"> All</a>
    <a href="{{ route('organizer.events.index', ['status' => 'upcoming']) }}" class="btn btn-outline-secondary btn-sm me-2"> Upcoming</a>
    <a href="{{ route('organizer.events.index', ['status' => 'ongoing']) }}" class="btn btn-outline-secondary btn-sm me-2">Ongoing</a>
    <a href="{{ route('organizer.events.index', ['status' => 'completed']) }}" class="btn btn-outline-secondary btn-sm me-2"> Completed</a>
    <a href="{{ route('organizer.events.index', ['status' => 'cancelled']) }}" class="btn btn-outline-secondary btn-sm"> Cancelled</a>

</div>

@if($events->isEmpty())

<div class="alert alert-info">You haven't created any events yet.</div>

@else

<div class="row">

@foreach($events as $event)

<div class="col-lg-6 mb-4">

    <div class="card shadow-sm h-100">

        @if($event->cover_photo)

        <img
        src="{{ $event->cover_photo }}"
        alt="{{ $event->title }}"
        class="card-img-top"
        style="height:220px; object-fit:cover;">

    @else

        <div class="d-flex align-items-center justify-content-center bg-light text-muted" style="height:220px;">No Event Banner</div>

        @endif
        <div class="card-body">

            <h5 class="fw-bold">

                {{ $event->title }}

            </h5>

            <p class="text-muted small mb-3">

            {{ $event->description }}

            </p>

            <p class="text-muted mb-2">

                <i class="fas fa-calendar me-2"></i>

                {{ \Carbon\Carbon::parse($event->event_date)->format('F d, Y') }}

            </p>

            <p class="text-muted mb-2">

                <i class="fas fa-map-marker-alt me-2"></i>

                {{ $event->venue }}

            </p>


            @if($event->status == 'upcoming')

            <span class="badge bg-primary">

            @elseif($event->status == 'ongoing')

            <span class="badge bg-success">

            @elseif($event->status == 'completed')

            <span class="badge bg-dark">

            @elseif($event->status == 'cancelled')

            <span class="badge bg-danger">

            @else

            <span class="badge bg-secondary">

            @endif

            {{ ucfirst($event->status) }}

            </span>

            <div class="mt-3">

                <a href="{{ route('organizer.events.show', $event) }}" class="btn btn-outline-primary btn-sm">View</a>

                <a href="{{ route('organizer.events.edit', $event) }}"
                class="btn btn-outline-secondary btn-sm" 
                onclick="return confirm('Editing this event may affect performers who have already accepted the bookings. Continue?')">

                    Edit
                </a>

            </div>

        </div>

    </div>

</div>

@endforeach

</div>

@endif

@endsection