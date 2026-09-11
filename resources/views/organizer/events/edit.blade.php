@extends('layouts.app')

@section('title', 'Edit Event')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@php
    $selectedCategoryIds = old('category_ids');

    if ($selectedCategoryIds === null) {
        $selectedCategoryIds = $event->categories->pluck('id')->all();
    }

    $selectedPreferredGenres = old('preferred_genres');

    if ($selectedPreferredGenres === null) {
        $selectedPreferredGenres = $event->preferred_genres;
    }

    if (! $selectedPreferredGenres) {
        $selectedPreferredGenres = [];
    }
@endphp
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
                <label class="form-label fw-semibold">Event Media</label>

                @if($event->photos->isNotEmpty())
                    <div class="row g-2 mb-3">
                        @foreach($event->photos as $photo)
                            <div class="col-4 col-md-3">
                                @if($photo->isVideo())
                                    <video class="rounded w-100 organizer-event-thumb" controls><source src="{{ $photo->fileUrl() }}"></video>
                                @else
                                    <img src="{{ $photo->fileUrl() }}" alt="" class="rounded w-100 organizer-event-thumb">
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif($event->coverPhotoUrl())
                    <div class="mb-3">
                        <img src="{{ $event->coverPhotoUrl() }}" alt="{{ $event->title }}" class="rounded organizer-event-preview">
                    </div>
                @else
                    <div class="mb-3 text-muted small">No media uploaded yet.</div>
                @endif

                <input
                    type="file"
                    name="photos[]"
                    class="form-control @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror"
                    accept="image/*"
                    multiple
                >
                <small class="text-muted d-block mb-2">Photos can be JPG, PNG, or WEBP, up to 5 MB each.</small>

                <label class="form-label mt-2">Add Videos</label>
                <input type="file" name="videos[]" class="form-control @error('videos') is-invalid @enderror @error('videos.*') is-invalid @enderror" accept="video/mp4,video/webm" multiple>
                <small class="text-muted">Videos can be MP4 or WEBM, up to 25 MB each. An event can have up to 3 photos and videos combined.</small>
                @error('photos')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('photos.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('videos')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('videos.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Event Name</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $event->title) }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Event Type</label>
                    <select class="form-select @error('event_type_id') is-invalid @enderror" name="event_type_id" id="event_type_id" required>
                        <option value="">Select Event Type</option>
                        @foreach($eventTypes as $eventType)
                            <option value="{{ $eventType->id }}" data-compensation-type="{{ $eventType->compensation_type }}" @selected((string) old('event_type_id', $event->event_type_id) === (string) $eventType->id)>
                                {{ $eventType->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('event_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Required Performer Categories</label>
                    <div class="border rounded p-2 @error('category_ids') border-danger @enderror">
                        @foreach($categories as $category)
                            <div class="form-check">
                                <input class="form-check-input event-category-checkbox" type="checkbox" name="category_ids[]" value="{{ $category->id }}" id="category-{{ $category->id }}" data-category-name="{{ $category->name }}" @checked(in_array($category->id, $selectedCategoryIds))>
                                <label class="form-check-label" for="category-{{ $category->id }}">{{ $category->name }}</label>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted">Select all performer categories needed for this event.</small>
                    @error('category_ids')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Preferred Genre / Style <span class="text-muted fw-normal">(Optional)</span></label>
                    <div class="organizer-category-list organizer-genre-list @error('preferred_genres') organizer-category-list-error @enderror">
                        <div class="row row-cols-2 g-2">
                        @foreach($genreCategories as $genre => $categoryNames)
                            <div class="col genre-option d-none" data-category-names="{{ implode('|', $categoryNames) }}">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="preferred_genres[]" value="{{ $genre }}" id="edit-genre-{{ $loop->index }}" @checked(in_array($genre, $selectedPreferredGenres))>
                                    <label class="form-check-label" for="edit-genre-{{ $loop->index }}">{{ $genre }}</label>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                    <small class="text-muted" id="genreHelp">Select a performer category first. You can then tick one or more relevant styles.</small>
                    @error('preferred_genres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('preferred_genres.*')<div class="text-danger small">{{ $message }}</div>@enderror
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
                    @php
                        $startTime = old('start_time');
                        if (! $startTime && $event->start_time) {
                            $startTime = \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i');
                        }
                    @endphp
                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" name="start_time" value="{{ $startTime }}" required>
                    @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">End Time</label>
                    @php
                        $endTime = old('end_time');
                        if (! $endTime && $event->end_time) {
                            $endTime = \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i');
                        }
                    @endphp
                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" name="end_time" value="{{ $endTime }}" required>
                    @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Venue / Location</label>
                <input type="text" class="form-control @error('venue') is-invalid @enderror" name="venue" value="{{ old('venue', $event->venue) }}" required>
                @error('venue')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row" id="compensationFields">
                <div class="col-md-6 mb-3 compensation-field d-none" data-compensation-type="fixed">
                    <label class="form-label">Fixed Budget (&#8369;)</label>
                    <input type="number" class="form-control @error('budget') is-invalid @enderror" name="budget" value="{{ old('budget', $event->budget) }}" min="0" step="0.01">
                    @error('budget')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3 compensation-field d-none" data-compensation-type="hourly">
                    <label class="form-label">Rate per Hour (&#8369;)</label>
                    <input type="number" class="form-control @error('rate_per_hour') is-invalid @enderror" name="rate_per_hour" value="{{ old('rate_per_hour', $event->rate_per_hour) }}" min="0" step="0.01">
                    @error('rate_per_hour')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-3 compensation-field d-none" data-compensation-type="contest">
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">First Prize (&#8369;)</label><input type="number" class="form-control" name="first_prize" value="{{ old('first_prize', $event->first_prize) }}" min="0" step="0.01"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Second Prize (&#8369;)</label><input type="number" class="form-control" name="second_prize" value="{{ old('second_prize', $event->second_prize) }}" min="0" step="0.01"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Third Prize (&#8369;)</label><input type="number" class="form-control" name="third_prize" value="{{ old('third_prize', $event->third_prize) }}" min="0" step="0.01"></div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Event Status</label>
                    @if($event->status === 'Completed')
                        <input type="hidden" name="status" value="Completed">
                        <input type="text" class="form-control" value="Completed" disabled>
                    @else
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="Open" @selected(old('status', $event->status) === 'Open')>Open</option>
                            <option value="Ended" @selected(old('status', $event->status) === 'Ended')>Ended</option>
                            <option value="Cancelled" @selected(old('status', $event->status) === 'Cancelled')>Cancelled</option>
                        </select>
                    @endif
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const eventType = document.getElementById('event_type_id');
    const compensationFields = document.querySelectorAll('.compensation-field');
    const categoryCheckboxes = document.querySelectorAll('.event-category-checkbox');
    const genreOptions = document.querySelectorAll('.genre-option');
    const genreHelp = document.getElementById('genreHelp');

    function showCompensationFields() {
        const selectedOption = eventType.options[eventType.selectedIndex];
        const compensationType = selectedOption.dataset.compensationType;

        compensationFields.forEach(function (field) {
            const isSelectedType = field.dataset.compensationType === compensationType;
            field.classList.toggle('d-none', !isSelectedType);
            field.querySelectorAll('input').forEach(function (input) {
                input.disabled = !isSelectedType;
            });
        });
    }

    function showRelevantGenres() {
        const selectedCategoryNames = [];

        categoryCheckboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                selectedCategoryNames.push(checkbox.dataset.categoryName);
            }
        });

        genreOptions.forEach(function (option) {
            const categoryNames = option.dataset.categoryNames.split('|');
            const genreCheckbox = option.querySelector('input');
            let isRelevant = false;

            categoryNames.forEach(function (categoryName) {
                if (selectedCategoryNames.includes(categoryName)) {
                    isRelevant = true;
                }
            });

            option.classList.toggle('d-none', !isRelevant);
            genreCheckbox.disabled = !isRelevant;

            if (!isRelevant) {
                genreCheckbox.checked = false;
            }
        });

        if (selectedCategoryNames.length === 0) {
            genreHelp.textContent = 'Select a performer category first. You can then tick one or more relevant styles.';
        } else {
            genreHelp.textContent = 'Tick one or more relevant styles. Categories remain required; styles only prioritize suggestions.';
        }
    }

    eventType.addEventListener('change', showCompensationFields);
    categoryCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', showRelevantGenres);
    });
    showCompensationFields();
    showRelevantGenres();
});
</script>
@endsection
