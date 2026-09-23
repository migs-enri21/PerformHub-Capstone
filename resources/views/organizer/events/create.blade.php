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
                    <input type="file" name="photos[]" class="form-control ph-input" multiple>
                    <small class="text-muted d-block">Photos can be JPG, PNG, or WEBP, up to 5 MB each.</small>
                    @error('photos')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('photos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Event Videos</label>
                    <input type="file" name="videos[]" class="form-control ph-input" multiple>
                    <small class="text-muted">Videos can be MP4 or WEBM, up to 25 MB each. You can upload up to 3 photos and videos combined.</small>
                    @error('videos')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('videos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Event Name</label>
                    <input type="text" class="form-control ph-input @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Event Type</label>
                        <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#featureRequestModal" data-request-type="event_type">
                            Request missing event type
                        </button>
                    </div>
                    <select class="form-select ph-input @error('event_type_id') is-invalid @enderror" name="event_type_id" id="event_type_id">
                        <option value="">Select Event Type</option>
                        @foreach($eventTypes as $eventType)
                            <option value="{{ $eventType->id }}" @selected(old('event_type_id') == $eventType->id)>
                                {{ $eventType->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('event_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Compensation Type</label>
                    <select class="form-select ph-input @error('compensation_type') is-invalid @enderror" name="compensation_type" id="compensation_type">
                        <option value="">Select Compensation Type</option>
                        <option value="fixed" @selected(old('compensation_type') === 'fixed')>Fixed Budget</option>
                        <option value="hourly" @selected(old('compensation_type') === 'hourly')>Rate per Hour</option>
                        <option value="contest" @selected(old('compensation_type') === 'contest')>Contest Prizes</option>
                    </select>
                    @error('compensation_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Required Performer Categories</label>
                        <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#featureRequestModal" data-request-type="category">
                            Request missing category
                        </button>
                    </div>
                    <div class="organizer-category-list @error('category_ids') organizer-category-list-error @enderror">
                        <div class="row row-cols-2 row-cols-md-3 g-2">
                            @foreach($categories as $category)
                                <div class="col">
                                    <div class="form-check">
                                        <input class="form-check-input event-category-checkbox" type="checkbox" name="category_ids[]" value="{{ $category->id }}" id="category-{{ $category->id }}" @checked(in_array($category->id, $selectedCategoryIds))>
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
                        @foreach($genreCategories as $genre => $categoryIds)
                            <div class="col genre-option d-none" data-category-ids="{{ implode('|', $categoryIds) }}">
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
                    <input type="date" class="form-control ph-input @error('event_date') is-invalid @enderror" name="event_date" value="{{ old('event_date') }}">
                    @error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Start Time</label>
                    <input type="time" class="form-control ph-input @error('start_time') is-invalid @enderror" name="start_time" value="{{ old('start_time') }}">
                    @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Time</label>
                    <input type="time" class="form-control ph-input @error('end_time') is-invalid @enderror" name="end_time" value="{{ old('end_time') }}">
                    @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Venue / Location</label>
                    <input type="text" class="form-control ph-input @error('venue') is-invalid @enderror" name="venue" value="{{ old('venue') }}">
                    @error('venue')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Special Requirements</label>
                    <textarea class="form-control ph-input" rows="4" name="description">{{ old('description') }}</textarea>
                </div>

                <div class="col-12 organizer-compensation-section">
                    <h5>Compensation Details</h5>
                    <p class="organizer-form-help">Choose a compensation type to show the correct payment fields.</p>

                    <div class="row g-4" id="compensationFields">
                        <div class="col-md-6 compensation-field d-none" data-compensation-type="fixed">
                            <label class="form-label">Fixed Budget (&#8369;)</label>
                            <input type="number" class="form-control ph-input" name="budget" value="{{ old('budget') }}" step="0.01">
                            @error('budget')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 compensation-field d-none" data-compensation-type="hourly">
                            <label class="form-label">Rate per Hour (&#8369;)</label>
                            <input type="number" class="form-control ph-input" name="rate_per_hour" value="{{ old('rate_per_hour') }}" step="0.01">
                            @error('rate_per_hour')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 compensation-field d-none" data-compensation-type="contest">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label">First Prize (&#8369;)</label>
                                    <input type="number" class="form-control ph-input" name="first_prize" value="{{ old('first_prize') }}" step="0.01">
                                    @error('first_prize')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Second Prize (&#8369;)</label>
                                    <input type="number" class="form-control ph-input" name="second_prize" value="{{ old('second_prize') }}" step="0.01">
                                    @error('second_prize')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Third Prize (&#8369;)</label>
                                    <input type="number" class="form-control ph-input" name="third_prize" value="{{ old('third_prize') }}" step="0.01">
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

<div class="modal fade" id="featureRequestModal" tabindex="-1" aria-labelledby="featureRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form method="POST" action="{{ route('organizer.feature-requests.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="type" id="featureRequestType" value="category">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="featureRequestModalLabel">Request an option</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Tell the admin what is missing. It will appear here after approval.</p>
                <label class="form-label" for="featureRequestName">Name</label>
                <input type="text" name="name" id="featureRequestName" class="form-control ph-input mb-3 @error('name') is-invalid @enderror" placeholder="Option name">
                @error('name')<div class="invalid-feedback mb-3">{{ $message }}</div>@enderror
                <label class="form-label" for="featureRequestDescription">Description <span class="text-muted fw-normal">(Optional)</span></label>
                <textarea name="description" id="featureRequestDescription" class="form-control ph-input" rows="3" placeholder="What is it used for?"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn ph-btn-primary">Send Request</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const compensationType = document.getElementById('compensation_type');
    const compensationFields = document.querySelectorAll('.compensation-field');
    const categoryCheckboxes = document.querySelectorAll('.event-category-checkbox');
    const genreOptions = document.querySelectorAll('.genre-option');
    const genreHelp = document.getElementById('genreHelp');
    const featureRequestModal = document.getElementById('featureRequestModal');
    const featureRequestType = document.getElementById('featureRequestType');
    const featureRequestName = document.getElementById('featureRequestName');
    const featureRequestModalLabel = document.getElementById('featureRequestModalLabel');

    featureRequestModal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        let type = 'category';

        if (trigger && trigger.dataset.requestType) {
            type = trigger.dataset.requestType;
        }
        featureRequestType.value = type;

        if (type === 'event_type') {
            featureRequestModalLabel.textContent = 'Request an event type';
            featureRequestName.placeholder = 'Event type name';
        } else {
            featureRequestModalLabel.textContent = 'Request a category';
            featureRequestName.placeholder = 'Category name';
        }
    });

    function showCompensationFields() {
        const selectedType = compensationType.value;

        compensationFields.forEach(function (field) {
            const isSelectedType = field.dataset.compensationType === selectedType;
            field.classList.toggle('d-none', !isSelectedType);

            field.querySelectorAll('input').forEach(function (input) {
                input.disabled = !isSelectedType;
            });
        });
    }

    function showRelevantGenres() {
        const selectedCategoryIds = [];

        categoryCheckboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                selectedCategoryIds.push(checkbox.value);
            }
        });

        genreOptions.forEach(function (option) {
            const categoryIds = option.dataset.categoryIds.split('|');
            const genreCheckbox = option.querySelector('input');
            let isRelevant = false;

            categoryIds.forEach(function (categoryId) {
                if (selectedCategoryIds.includes(categoryId)) {
                    isRelevant = true;
                }
            });

            option.classList.toggle('d-none', !isRelevant);
            genreCheckbox.disabled = !isRelevant;

            if (!isRelevant) {
                genreCheckbox.checked = false;
            }
        });

        if (selectedCategoryIds.length === 0) {
            genreHelp.textContent = 'Select a performer category first. You can then tick one or more relevant styles.';
        } else {
            genreHelp.textContent = 'Tick one or more relevant styles. Categories are still required; styles only prioritize suggestions.';
        }
    }

    compensationType.addEventListener('change', showCompensationFields);
    categoryCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', showRelevantGenres);
    });
    showCompensationFields();
    showRelevantGenres();
});
</script>
@endsection
