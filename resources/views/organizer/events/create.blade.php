@extends('layouts.app')

@section('title', 'Create Event')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@php
    $selectedCategoryIds = old('category_ids', []);
@endphp

<div class="container organizer-event-form">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Create Event</h2>
        <p class="text-muted mb-0">Add your event details and choose the performer categories you need.</p>
    </div>

    <div class="ph-card p-4 p-lg-5">
        <form method="POST" action="{{ route('organizer.events.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label fw-semibold">Event Photos</label>
                    <input type="file" name="photos[]" class="form-control ph-input" accept="image/*" multiple>
                    <small class="text-muted">Upload one or more photos, up to 5 MB each.</small>
                </div>

                <div class="col-12">
                    <label class="form-label">Event Name</label>
                    <input type="text" class="form-control ph-input" name="title" value="{{ old('title') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Event Type</label>
                    <select class="form-select ph-input" name="event_type_id" required>
                        <option value="">Select Event Type</option>
                        @foreach($eventTypes as $eventType)
                            <option value="{{ $eventType->id }}" @selected(old('event_type_id') == $eventType->id)>
                                {{ $eventType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Budget (₱)</label>
                    <input type="number" class="form-control ph-input" name="budget" value="{{ old('budget') }}" min="0" step="0.01">
                </div>

                <div class="col-12">
                    <label class="form-label">Required Performer Categories</label>
                    <div class="organizer-category-list @error('category_ids') organizer-category-list-error @enderror">
                        <div class="row row-cols-2 row-cols-md-3 g-2">
                            @foreach($categories as $category)
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="category_ids[]" value="{{ $category->id }}" id="category-{{ $category->id }}" @checked(in_array($category->id, $selectedCategoryIds))>
                                        <label class="form-check-label" for="category-{{ $category->id }}">{{ $category->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <small class="text-muted">Select all performer categories needed for this event.</small>
                    @error('category_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Event Date</label>
                    <input type="date" class="form-control ph-input" name="event_date" value="{{ old('event_date') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Time</label>
                    <input type="time" class="form-control ph-input" name="start_time" value="{{ old('start_time') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Time</label>
                    <input type="time" class="form-control ph-input" name="end_time" value="{{ old('end_time') }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Venue / Location</label>
                    <input type="text" class="form-control ph-input" name="venue" value="{{ old('venue') }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Special Requirements</label>
                    <textarea class="form-control ph-input" rows="4" name="description">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('organizer.events.index') }}" class="btn ph-btn-secondary">Cancel</a>
                <button type="submit" class="btn ph-btn-primary">Create Event</button>
            </div>
        </form>
    </div>
</div>
@endsection
