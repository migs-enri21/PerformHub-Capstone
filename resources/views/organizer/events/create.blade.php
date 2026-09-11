@extends('layouts.app')

@section('title', 'Create Event')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@php
    $selectedCategoryIds = old('category_ids', []);
    $selectedPreferredGenres = old('preferred_genres', []);
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
                    <small class="text-muted d-block">Photos can be JPG, PNG, or WEBP, up to 5 MB each.</small>
                    @error('photos')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('photos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Event Videos</label>
                    <input type="file" name="videos[]" class="form-control ph-input" accept="video/mp4,video/webm" multiple>
                    <small class="text-muted">Videos can be MP4 or WEBM, up to 25 MB each. You can upload up to 3 photos and videos combined.</small>
                    @error('videos')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('videos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Event Name</label>
                    <input type="text" class="form-control ph-input" name="title" value="{{ old('title') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Event Type</label>
                    <select class="form-select ph-input" name="event_type_id" id="event_type_id" required>
                        <option value="">Select Event Type</option>
                        @foreach($eventTypes as $eventType)
                            <option value="{{ $eventType->id }}" data-compensation-type="{{ $eventType->compensation_type }}" @selected(old('event_type_id') == $eventType->id)>
                                {{ $eventType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 compensation-field d-none" data-compensation-type="fixed">
                    <label class="form-label">Budget (₱)</label>
                    <input type="number" class="form-control ph-input" name="budget" value="{{ old('budget') }}" min="0" step="0.01">
                    @error('budget')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Required Performer Categories</label>
                    <div class="organizer-category-list @error('category_ids') organizer-category-list-error @enderror">
                        <div class="row row-cols-2 row-cols-md-3 g-2">
                            @foreach($categories as $category)
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input event-category-checkbox" type="checkbox" name="category_ids[]" value="{{ $category->id }}" id="category-{{ $category->id }}" data-category-name="{{ $category->name }}" @checked(in_array($category->id, $selectedCategoryIds))>
                                        <label class="form-check-label" for="category-{{ $category->id }}">{{ $category->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <small class="text-muted">Select all performer categories needed for this event.</small>
                    @error('category_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Preferred Genre / Style <span class="text-muted fw-normal">(Optional)</span></label>
                    <div class="organizer-category-list organizer-genre-list @error('preferred_genres') organizer-category-list-error @enderror">
                        <div class="row row-cols-2 row-cols-md-3 g-2">
                        @foreach($genreCategories as $genre => $categoryNames)
                            <div class="col genre-option d-none" data-category-names="{{ implode('|', $categoryNames) }}">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="preferred_genres[]" value="{{ $genre }}" id="create-genre-{{ $loop->index }}" @checked(in_array($genre, $selectedPreferredGenres))>
                                    <label class="form-check-label" for="create-genre-{{ $loop->index }}">{{ $genre }}</label>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                    <small class="text-muted" id="genreHelp">Select a performer category first. You can then tick one or more relevant styles.</small>
                    @error('preferred_genres')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('preferred_genres.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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

                <div class="col-12 organizer-compensation-section">
                    <h5>Compensation Details</h5>
                    <p class="organizer-form-help">Choose an event type first to show the correct compensation fields.</p>

                    <div class="row g-4" id="compensationFields">
                        <div class="col-md-6 compensation-field d-none" data-compensation-type="hourly">
                            <label class="form-label">Rate per Hour (&#8369;)</label>
                            <input type="number" class="form-control ph-input" name="rate_per_hour" value="{{ old('rate_per_hour') }}" min="0" step="0.01">
                            @error('rate_per_hour')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 compensation-field d-none" data-compensation-type="contest">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label">First Prize (&#8369;)</label>
                                    <input type="number" class="form-control ph-input" name="first_prize" value="{{ old('first_prize') }}" min="0" step="0.01">
                                    @error('first_prize')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Second Prize (&#8369;)</label>
                                    <input type="number" class="form-control ph-input" name="second_prize" value="{{ old('second_prize') }}" min="0" step="0.01">
                                    @error('second_prize')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Third Prize (&#8369;)</label>
                                    <input type="number" class="form-control ph-input" name="third_prize" value="{{ old('third_prize') }}" min="0" step="0.01">
                                    @error('third_prize')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('organizer.events.index') }}" class="btn ph-btn-secondary">Cancel</a>
                <button type="submit" class="btn ph-btn-primary">Create Event</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const eventType = document.getElementById('event_type_id');
    const compensationBox = document.getElementById('compensationFields');
    const compensationFields = document.querySelectorAll('.compensation-field');
    const fixedBudgetField = document.querySelector('.compensation-field[data-compensation-type="fixed"]');
    const categoryCheckboxes = document.querySelectorAll('.event-category-checkbox');
    const genreOptions = document.querySelectorAll('.genre-option');
    const genreHelp = document.getElementById('genreHelp');

    if (fixedBudgetField) {
        compensationBox.prepend(fixedBudgetField);
    }

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
            genreHelp.textContent = 'Tick one or more relevant styles. Categories are still required; styles only prioritize suggestions.';
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
