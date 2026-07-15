@extends('layouts.app')

@section('title', 'Create Event')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')

<div class="container">

    <h2 class="fw-bold mb-4">Event Information</h2>

    <div class="ph-card p-4">
        
            <form method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data">
             @csrf

                <div class="mb-4">

                <label class="form-label fw-semibold"> Event Banner Photo</label>

                <input
                type="file"
                name="banner_photo"
                class="form-control"
                accept="image/*">

                <small class="text-muted"> Upload a banner photo for your event.</small>

                </div>
                <div class="mb-3"><label class="form-label">Event Name</label><input type="text" class="form-control" name="title"></div>

                <div class="row">

                    <div class="col-md-6 mb-3"><label class="form-label">Event Type</label><select class="form-select" name="event_type_id" required>
                    <option value="">Select Event Type</option>
                    @foreach($eventTypes as $eventType)
                    <option value="{{ $eventType->id }}">
                        {{ $eventType->name }}
                    </option>
                    @endforeach
                    </select>
                    </div>

                    <div class="col-md-6 mb-3"><label class="form-label">Preferred Performer Category</label><select class="form-select" name="category_id">
                        <select class="form-select" disabled>
                        <option>Coming Soon</option>
                    </select>
                    </select>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3"><label class="form-label">Event Date</label><input type="date" class="form-control" name="event_date"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Event Time</label><input type="time" class="form-control" name="start_time"></div>

                </div>

                <div class="mb-3"><label class="form-label">Venue / Location</label><input type="text" class="form-control" name="venue"></div>

                <div class="row">

                    <div class="col-md-6 mb-3"><label class="form-label">Budget (₱)</label><input type="number" class="form-control" name="budget"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Number of Performers Needed</label><input type="number" class="form-control" name="performers_needed" min="1" value="1"></div>

                </div>

                <div class="mb-4"><label class="form-label">Special Requirements</label><textarea class="form-control" rows="4" name="description"></textarea></div>

                <div class="d-flex justify-content-end">

                    <a href="{{ route('organizer.dashboard') }}" class="btn ph-btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn ph-btn-primary">Create Event & Find Performers</button>

                </div>

            </form>

        
    </div>

</div>

@endsection