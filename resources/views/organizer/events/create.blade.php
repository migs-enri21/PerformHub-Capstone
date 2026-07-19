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

                <label class="form-label fw-semibold">Event Photos</label>

                <input
                type="file"
                name="photos[]"
                class="form-control"
                accept="image/*"
                multiple>

                <small class="text-muted">Upload one or more photos. Multiple photos show as a collage for performers.</small>

                </div>
                <div class="mb-3"><label class="form-label">Event Name</label><input type="text" class="form-control" name="title" value="{{ old('title') }}"></div>

                <div class="row">

                    <div class="col-md-6 mb-3"><label class="form-label">Event Type</label><select class="form-select" name="event_type_id" required>
                    <option value="">Select Event Type</option>
                    @foreach($eventTypes as $eventType)
                    <option value="{{ $eventType->id }}" {{ old('event_type_id') == $eventType->id ? 'selected' : '' }}>
                        {{ $eventType->name }}
                    </option>
                    @endforeach
                    </select>
                    </div>

                    <div class="col-md-6 mb-3"><label class="form-label">Preferred Performer Category</label>

                    <select name="preferred_category_id" class="form-select">

                    <option value="">Select Performer Category</option>

                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('preferred_category_id') == $category->id ? 'selected' : '' }}> {{ $category->name }}
                    </option>
                    @endforeach

                </select>
                </div>

                </div>

                <div class="row">

                <div class="col-md-4 mb-3"><label class="form-label">Event Date</label><input type="date" class="form-control" name="event_date" value="{{ old('event_date') }}"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Start Time</label><input type="time" class="form-control" name="start_time" value="{{ old('start_time') }}"></div>

                <div class="col-md-4 mb-3"><label class="form-label">End Time</label><input type="time" class="form-control" name="end_time" value="{{ old('end_time') }}"></div>
                </div>

                <div class="mb-3"><label class="form-label">Venue / Location</label><input type="text" class="form-control" name="venue" value="{{ old('venue') }}"></div>

                <div class="row">

                    <div class="col-md-6 mb-3"><label class="form-label">Budget (₱)</label><input type="number" class="form-control" name="budget" value="{{ old('budget') }}"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Number of Performers Needed</label><input type="number" class="form-control" name="performers_needed" min="1" value="{{ old('performers_needed',1) }}"></div>

                </div>

                <div class="mb-4"><label class="form-label">Special Requirements</label><textarea class="form-control" rows="4" name="description">{{ old('description') }}</textarea></div>

                <div class="d-flex justify-content-end">

                    <a href="{{ route('organizer.dashboard') }}" class="btn ph-btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn ph-btn-primary">Create Event & Find Performers</button>

                </div>

            </form>

        
    </div>

</div>

@endsection