@extends('layouts.app')

@section('title', 'Edit Event')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Edit Event</h2>
        <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" onsubmit="return confirm('Delete this event permanently?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">Delete Event</button>
        </form>
    </div>

    <div class="ph-card p-4">
        <form method="POST" action="{{ route('organizer.events.update', $event) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label fw-semibold">Event Photos</label>

                @if($event->photos->isNotEmpty())
                    <div class="row g-2 mb-3">
                        @foreach($event->photos as $photo)
                            <div class="col-4 col-md-3">
                                <img src="{{ $photo->fileUrl() }}" alt="" class="rounded w-100 organizer-event-thumb">
                            </div>
                        @endforeach
                    </div>
                @elseif($event->coverPhotoUrl())
                    <div class="mb-3">
                        <img src="{{ $event->coverPhotoUrl() }}" alt="{{ $event->title }}" class="rounded organizer-event-preview">
                    </div>
                @else
                    <div class="mb-3 text-muted small">No photos uploaded yet.</div>
                @endif

                <input
                    type="file"
                    name="photos[]"
                    class="form-control @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror"
                    accept="image/*"
                    multiple
                >
                <small class="text-muted">Add more photos. Multiple photos show as a collage for performers.</small>
                @error('photos')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('photos.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Event Name</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $event->title) }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Event Type</label>
                    <select class="form-select @error('event_type_id') is-invalid @enderror" name="event_type_id" required>
                        <option value="">Select Event Type</option>
                        @foreach($eventTypes as $eventType)
                            <option value="{{ $eventType->id }}" {{ (string) old('event_type_id', $event->event_type_id) === (string) $eventType->id ? 'selected' : '' }}>
                                {{ $eventType->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('event_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Preferred Performer Category</label>
                    <select name="preferred_category_id" class="form-select @error('preferred_category_id') is-invalid @enderror">
                        <option value="">Select Performer Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('preferred_category_id', $event->preferred_category_id) === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('preferred_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Event Date</label>
                    <input type="date" class="form-control @error('event_date') is-invalid @enderror" name="event_date" value="{{ old('event_date', \Illuminate\Support\Carbon::parse($event->event_date)->format('Y-m-d')) }}" required>
                    @error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Start Time</label>
                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" name="start_time" value="{{ old('start_time', $event->start_time ? \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i') : '') }}" required>
                    @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">End Time</label>
                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" name="end_time" value="{{ old('end_time', $event->end_time ? \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') : '') }}" required>
                    @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Venue / Location</label>
                <input type="text" class="form-control @error('venue') is-invalid @enderror" name="venue" value="{{ old('venue', $event->venue) }}" required>
                @error('venue')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Budget (₱)</label>
                    <input type="number" class="form-control @error('budget') is-invalid @enderror" name="budget" value="{{ old('budget', $event->budget) }}">
                    @error('budget')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Number of Performers Needed</label>
                    <input type="number" class="form-control @error('performers_needed') is-invalid @enderror" name="performers_needed" min="1" value="{{ old('performers_needed', $event->performers_needed) }}" required>
                    @error('performers_needed')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Special Requirements</label>
                <textarea class="form-control @error('description') is-invalid @enderror" rows="4" name="description">{{ old('description', $event->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('organizer.events.index') }}" class="btn ph-btn-secondary">Cancel</a>
                <button type="submit" class="btn ph-btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
