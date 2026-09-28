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

    $selectedCompensationType = old('compensation_type');
    if (! $selectedCompensationType) {
        $selectedCompensationType = $event->compensation_type;
    }

    $pendingEventTypeRequests = $eventTypeRequests->where('status', 'pending')->count();
    $pendingCategoryRequests = $categoryRequests->where('status', 'pending')->count();
    $startTime = old('start_time');
    $endTime = old('end_time');

    if (! $startTime && $event->start_time) {
        $startTime = \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i');
    }
    if (! $endTime && $event->end_time) {
        $endTime = \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i');
    }
@endphp

<div class="container organizer-event-form">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Event</h2>
            <p class="text-muted mb-0">Update your event details and performer requirements.</p>
        </div>
        <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" class="organizer-confirm-form" data-confirm-title="Delete Event" data-confirm-message="Delete this event permanently? This cannot be undone." data-confirm-button="Delete Event">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Delete Event</button>
        </form>
    </div>

    <div class="ph-card p-4 p-lg-5">
        <form method="POST" action="{{ route('organizer.events.update', $event) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label fw-semibold">Current Event Media</label>
                    @if($event->photos->isNotEmpty())
                        <div class="row g-2">
                            @foreach($event->photos as $photo)
                                <div class="col-4 col-md-3">
                                    <div class="organizer-edit-media-item">
                                        @if($photo->isVideo())
                                            <video class="rounded w-100 organizer-event-thumb" controls><source src="{{ $photo->fileUrl() }}"></video>
                                        @else
                                            <button type="button" class="organizer-media-preview-button organizer-image-preview-trigger" data-image-url="{{ $photo->fileUrl() }}">
                                                <img src="{{ $photo->fileUrl() }}" alt="Event photo" class="rounded w-100 organizer-event-thumb">
                                            </button>
                                        @endif
                                        <form method="POST" action="{{ route('organizer.events.media.destroy', [$event, $photo]) }}" class="organizer-edit-media-remove organizer-confirm-form" data-confirm-title="Remove Media" data-confirm-message="Remove this media from the event? This cannot be undone." data-confirm-button="Remove Media">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="Remove event media">&times;</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($event->coverPhotoUrl())
                        <button type="button" class="organizer-media-preview-button organizer-image-preview-trigger" data-image-url="{{ $event->coverPhotoUrl() }}">
                            <img src="{{ $event->coverPhotoUrl() }}" alt="{{ $event->title }}" class="rounded organizer-event-preview">
                        </button>
                    @else
                        <p class="text-muted small mb-0">No media uploaded yet.</p>
                    @endif
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Add Event Photos</label>
                    <input type="file" name="photos[]" id="eventPhotos" class="form-control ph-input @error('photos') is-invalid @enderror @error('photos.*') is-invalid @enderror" multiple>
                    <small class="text-muted d-block">Photos can be JPG, PNG, or WEBP, up to 5 MB each.</small>
                    <div class="row g-2 mt-1" id="eventPhotoPreview"></div>
                    @error('photos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('photos.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Add Event Videos</label>
                    <input type="file" name="videos[]" id="eventVideos" class="form-control ph-input @error('videos') is-invalid @enderror @error('videos.*') is-invalid @enderror" multiple>
                    <small class="text-muted">Videos can be MP4 or WEBM, up to 25 MB each. You can upload up to 3 photos and videos combined.</small>
                    <div class="row g-2 mt-1" id="eventVideoPreview"></div>
                    @error('videos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('videos.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Event Name</label>
                    <input type="text" class="form-control ph-input @error('title') is-invalid @enderror" name="title" value="{{ old('title', $event->title) }}">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <button type="button" class="form-label mb-0 organizer-request-title" data-bs-toggle="modal" data-bs-target="#eventTypeRequestsModal">
                            Event Type
                            @if($pendingEventTypeRequests > 0)
                                <span class="organizer-request-count">{{ $pendingEventTypeRequests }}</span>
                            @endif
                        </button>
                        <button type="button" class="organizer-request-option" data-bs-toggle="modal" data-bs-target="#featureRequestModal" data-request-type="event_type"><i class="fas fa-plus-circle"></i> Request event type</button>
                    </div>
                    <select class="form-select ph-input @error('event_type_id') is-invalid @enderror" name="event_type_id" id="event_type_id">
                        <option value="">Select Event Type</option>
                        @foreach($eventTypes as $eventType)
                            <option value="{{ $eventType->id }}" @selected((string) old('event_type_id', $event->event_type_id) === (string) $eventType->id)>{{ $eventType->name }}</option>
                        @endforeach
                    </select>
                    @error('event_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Compensation Type</label>
                    <select class="form-select ph-input @error('compensation_type') is-invalid @enderror" name="compensation_type" id="compensation_type">
                        <option value="">Select Compensation Type</option>
                        <option value="fixed" @selected($selectedCompensationType === 'fixed')>Fixed Budget</option>
                        <option value="hourly" @selected($selectedCompensationType === 'hourly')>Rate per Hour</option>
                        <option value="contest" @selected($selectedCompensationType === 'contest')>Contest Prizes</option>
                    </select>
                    @error('compensation_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <button type="button" class="form-label mb-0 organizer-request-title" data-bs-toggle="modal" data-bs-target="#categoryRequestsModal">
                            Required Performer Categories
                            @if($pendingCategoryRequests > 0)
                                <span class="organizer-request-count">{{ $pendingCategoryRequests }}</span>
                            @endif
                        </button>
                        <button type="button" class="organizer-request-option" data-bs-toggle="modal" data-bs-target="#featureRequestModal" data-request-type="category"><i class="fas fa-plus-circle"></i> Request category</button>
                    </div>
                    <div class="organizer-category-list @error('category_ids') organizer-category-list-error @enderror">
                        <div class="row row-cols-2 row-cols-md-3 g-2">
                            @foreach($categories as $category)
                                <div class="col"><div class="form-check">
                                    <input class="form-check-input event-category-checkbox" type="checkbox" name="category_ids[]" value="{{ $category->id }}" id="category-{{ $category->id }}" @checked(in_array($category->id, $selectedCategoryIds))>
                                    <label class="form-check-label" for="category-{{ $category->id }}">{{ $category->name }}</label>
                                </div></div>
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
                                <div class="col genre-option d-none" data-category-ids="{{ implode('|', $categoryIds) }}"><div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="preferred_genres[]" value="{{ $genre }}" id="edit-genre-{{ $loop->index }}" @checked(in_array($genre, $selectedPreferredGenres))>
                                    <label class="form-check-label" for="edit-genre-{{ $loop->index }}">{{ $genre }}</label>
                                </div></div>
                            @endforeach
                        </div>
                    </div>
                    <small class="text-muted" id="genreHelp">Select a performer category first. You can then tick one or more relevant styles.</small>
                    @error('preferred_genres')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @error('preferred_genres.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Event Date</label>
                    <input type="date" class="form-control ph-input @error('event_date') is-invalid @enderror" name="event_date" value="{{ old('event_date', \Illuminate\Support\Carbon::parse($event->event_date)->format('Y-m-d')) }}" min="{{ now()->toDateString() }}">
                    @error('event_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted d-block mt-1">Up to 3 active events may be scheduled on one day, with a 3-hour gap between them.</small>
                </div>
                <div class="col-md-4"><label class="form-label">Start Time</label><input type="time" class="form-control ph-input @error('start_time') is-invalid @enderror" name="start_time" value="{{ $startTime }}">@error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label">End Time</label><input type="time" class="form-control ph-input @error('end_time') is-invalid @enderror" name="end_time" value="{{ $endTime }}">@error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>

                <div class="col-12"><label class="form-label">Venue / Location</label><input type="text" class="form-control ph-input @error('venue') is-invalid @enderror" name="venue" value="{{ old('venue', $event->venue) }}">@error('venue')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><label class="form-label">Special Requirements</label><textarea class="form-control ph-input @error('description') is-invalid @enderror" rows="4" name="description">{{ old('description', $event->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>

                <div class="col-12 organizer-compensation-section">
                    <h5>Compensation Details</h5>
                    <p class="organizer-form-help">Choose a compensation type to show the correct payment fields.</p>
                    <div class="row g-4" id="compensationFields">
                        <div class="col-md-6 compensation-field d-none" data-compensation-type="fixed"><label class="form-label">Fixed Budget (&#8369;)</label><input type="number" class="form-control ph-input @error('budget') is-invalid @enderror" name="budget" value="{{ old('budget', $event->budget) }}" step="0.01">@error('budget')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6 compensation-field d-none" data-compensation-type="hourly"><label class="form-label">Rate per Hour (&#8369;)</label><input type="number" class="form-control ph-input @error('rate_per_hour') is-invalid @enderror" name="rate_per_hour" value="{{ old('rate_per_hour', $event->rate_per_hour) }}" step="0.01">@error('rate_per_hour')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-12 compensation-field d-none" data-compensation-type="contest"><div class="row g-4">
                            <div class="col-md-4"><label class="form-label">First Prize (&#8369;)</label><input type="number" class="form-control ph-input @error('first_prize') is-invalid @enderror" name="first_prize" id="first_prize" value="{{ old('first_prize', $event->first_prize) }}" step="0.01">@error('first_prize')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-4"><label class="form-label">Second Prize (&#8369;)</label><input type="number" class="form-control ph-input @error('second_prize') is-invalid @enderror" name="second_prize" id="second_prize" value="{{ old('second_prize', $event->second_prize) }}" step="0.01">@error('second_prize')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-4"><label class="form-label">Third Prize (&#8369;)</label><input type="number" class="form-control ph-input @error('third_prize') is-invalid @enderror" name="third_prize" id="third_prize" value="{{ old('third_prize', $event->third_prize) }}" step="0.01">@error('third_prize')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        </div><div class="text-danger small mt-2 d-none" id="contestPrizeOrderError"></div></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Event Status</label>
                    @if($event->status === 'Completed')
                        <input type="hidden" name="status" value="Completed"><input type="text" class="form-control ph-input" value="Completed" disabled>
                    @else
                        <select name="status" class="form-select ph-input @error('status') is-invalid @enderror"><option value="Open" @selected(old('status', $event->status) === 'Open')>Open</option><option value="Ended" @selected(old('status', $event->status) === 'Ended')>Ended</option><option value="Cancelled" @selected(old('status', $event->status) === 'Cancelled')>Cancelled</option></select>
                    @endif
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top"><a href="{{ route('organizer.events.index') }}" class="btn ph-btn-secondary">Cancel</a><button type="submit" class="btn ph-btn-primary">Save Changes</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="eventImagePreviewModal" tabindex="-1" aria-labelledby="eventImagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventImagePreviewModalLabel">Event Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center"><img src="" alt="Event photo preview" class="img-fluid" id="eventImagePreview"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventTypeRequestsModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title fw-bold">My Event Type Requests</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">@include('organizer.events.partials.feature-request-list', ['requests' => $eventTypeRequests, 'requestType' => 'event_type'])</div></div></div></div>
<div class="modal fade" id="categoryRequestsModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title fw-bold">My Category Requests</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">@include('organizer.events.partials.feature-request-list', ['requests' => $categoryRequests, 'requestType' => 'category'])</div></div></div></div>

<div class="modal fade" id="featureRequestModal" tabindex="-1" aria-labelledby="featureRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm"><form method="POST" action="{{ route('organizer.feature-requests.store') }}" class="modal-content">
        @csrf
        <input type="hidden" name="type" id="featureRequestType" value="category">
        <div class="modal-header"><h5 class="modal-title fw-bold" id="featureRequestModalLabel">Request an option</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><p class="text-muted small">Tell the admin what is missing. It will appear here after approval.</p><label class="form-label" for="featureRequestName">Name</label><input type="text" name="name" id="featureRequestName" class="form-control ph-input mb-3 @error('name') is-invalid @enderror" placeholder="Option name">@error('name')<div class="invalid-feedback mb-3">{{ $message }}</div>@enderror<label class="form-label" for="featureRequestDescription">Description <span class="text-muted fw-normal">(Optional)</span></label><textarea name="description" id="featureRequestDescription" class="form-control ph-input" rows="3" placeholder="What is it used for?"></textarea></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn ph-btn-primary">Send Request</button></div>
    </form></div>
</div>

@include('organizer.partials.confirmation-modal')

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
    const eventPhotos = document.getElementById('eventPhotos');
    const eventPhotoPreview = document.getElementById('eventPhotoPreview');
    const eventVideos = document.getElementById('eventVideos');
    const eventVideoPreview = document.getElementById('eventVideoPreview');
    const eventImagePreviewModal = document.getElementById('eventImagePreviewModal');
    const eventImagePreview = document.getElementById('eventImagePreview');
    const firstPrize = document.getElementById('first_prize');
    const secondPrize = document.getElementById('second_prize');
    const thirdPrize = document.getElementById('third_prize');
    const contestPrizeOrderError = document.getElementById('contestPrizeOrderError');

    function openImagePreview(imageUrl) {
        eventImagePreview.src = imageUrl;
        bootstrap.Modal.getOrCreateInstance(eventImagePreviewModal).show();
    }

    document.querySelectorAll('.organizer-image-preview-trigger').forEach(function (button) {
        button.addEventListener('click', function () {
            openImagePreview(button.dataset.imageUrl);
        });
    });

    function showSelectedPhotos() {
        eventPhotoPreview.innerHTML = '';

        Array.from(eventPhotos.files).forEach(function (file, index) {
            if (!file.type.startsWith('image/')) {
                return;
            }

            const imageUrl = URL.createObjectURL(file);
            const column = document.createElement('div');
            const mediaItem = document.createElement('div');
            const button = document.createElement('button');
            const image = document.createElement('img');
            const removeButton = document.createElement('button');

            column.className = 'col-4 col-md-3';
            mediaItem.className = 'organizer-edit-media-item';
            button.type = 'button';
            button.className = 'organizer-media-preview-button';
            image.src = imageUrl;
            image.alt = 'Selected event photo';
            image.className = 'rounded w-100 organizer-event-thumb';
            button.appendChild(image);
            button.addEventListener('click', function () {
                openImagePreview(imageUrl);
            });
            removeButton.type = 'button';
            removeButton.className = 'organizer-selected-media-remove';
            removeButton.setAttribute('aria-label', 'Remove selected photo');
            removeButton.textContent = '×';
            removeButton.addEventListener('click', function () {
                removeSelectedFile(eventPhotos, index);
                showSelectedPhotos();
            });
            mediaItem.appendChild(button);
            mediaItem.appendChild(removeButton);
            column.appendChild(mediaItem);
            eventPhotoPreview.appendChild(column);
        });
    }

    function showSelectedVideos() {
        eventVideoPreview.innerHTML = '';

        Array.from(eventVideos.files).forEach(function (file, index) {
            if (!file.type.startsWith('video/')) {
                return;
            }

            const videoUrl = URL.createObjectURL(file);
            const column = document.createElement('div');
            const mediaItem = document.createElement('div');
            const video = document.createElement('video');
            const removeButton = document.createElement('button');

            column.className = 'col-4 col-md-3';
            mediaItem.className = 'organizer-edit-media-item';
            video.src = videoUrl;
            video.controls = true;
            video.className = 'rounded w-100 organizer-event-thumb';
            removeButton.type = 'button';
            removeButton.className = 'organizer-selected-media-remove';
            removeButton.setAttribute('aria-label', 'Remove selected video');
            removeButton.textContent = '×';
            removeButton.addEventListener('click', function () {
                removeSelectedFile(eventVideos, index);
                showSelectedVideos();
            });
            mediaItem.appendChild(video);
            mediaItem.appendChild(removeButton);
            column.appendChild(mediaItem);
            eventVideoPreview.appendChild(column);
        });
    }

    function removeSelectedFile(input, index) {
        const selectedFiles = Array.from(input.files);
        const updatedFiles = new DataTransfer();

        selectedFiles.forEach(function (file, fileIndex) {
            if (fileIndex !== index) {
                updatedFiles.items.add(file);
            }
        });

        input.files = updatedFiles.files;
    }

    featureRequestModal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        let type = 'category';
        if (trigger && trigger.dataset.requestType) { type = trigger.dataset.requestType; }
        featureRequestType.value = type;
        if (type === 'event_type') { featureRequestModalLabel.textContent = 'Request an event type'; featureRequestName.placeholder = 'Event type name'; } else { featureRequestModalLabel.textContent = 'Request a category'; featureRequestName.placeholder = 'Category name'; }
    });

    function showCompensationFields() {
        const selectedType = compensationType.value;
        compensationFields.forEach(function (field) {
            const isSelectedType = field.dataset.compensationType === selectedType;
            field.classList.toggle('d-none', !isSelectedType);
            field.querySelectorAll('input').forEach(function (input) { input.disabled = !isSelectedType; });
        });

        checkPrizeOrder();
    }

    function checkPrizeOrder() {
        const prizeInputs = [firstPrize, secondPrize, thirdPrize];
        let message = '';

        prizeInputs.forEach(function (input) {
            if (input.dataset.prizeOrderError === 'true') {
                input.classList.remove('is-invalid');
                input.setCustomValidity('');
                delete input.dataset.prizeOrderError;
            }
        });

        if (compensationType.value !== 'contest') {
            contestPrizeOrderError.classList.add('d-none');
            return;
        }

        if (firstPrize.value !== '' && secondPrize.value !== '' && Number(firstPrize.value) <= Number(secondPrize.value)) {
            message = 'First prize must be higher than second prize.';
            firstPrize.classList.add('is-invalid');
            firstPrize.setCustomValidity(message);
            firstPrize.dataset.prizeOrderError = 'true';
        }

        if (message === '' && secondPrize.value !== '' && thirdPrize.value !== '' && Number(secondPrize.value) <= Number(thirdPrize.value)) {
            message = 'Second prize must be higher than third prize.';
            secondPrize.classList.add('is-invalid');
            secondPrize.setCustomValidity(message);
            secondPrize.dataset.prizeOrderError = 'true';
        }

        if (message === '' && firstPrize.value !== '' && thirdPrize.value !== '' && Number(firstPrize.value) <= Number(thirdPrize.value)) {
            message = 'First prize must be higher than third prize.';
            firstPrize.classList.add('is-invalid');
            firstPrize.setCustomValidity(message);
            firstPrize.dataset.prizeOrderError = 'true';
        }

        contestPrizeOrderError.textContent = message;
        contestPrizeOrderError.classList.toggle('d-none', message === '');
    }

    function showRelevantGenres() {
        const selectedCategoryIds = [];
        categoryCheckboxes.forEach(function (checkbox) { if (checkbox.checked) { selectedCategoryIds.push(checkbox.value); } });
        genreOptions.forEach(function (option) {
            const categoryIds = option.dataset.categoryIds.split('|');
            const genreCheckbox = option.querySelector('input');
            let isRelevant = false;
            categoryIds.forEach(function (categoryId) { if (selectedCategoryIds.includes(categoryId)) { isRelevant = true; } });
            option.classList.toggle('d-none', !isRelevant);
            genreCheckbox.disabled = !isRelevant;
            if (!isRelevant) { genreCheckbox.checked = false; }
        });
        if (selectedCategoryIds.length === 0) { genreHelp.textContent = 'Select a performer category first. You can then tick one or more relevant styles.'; } else { genreHelp.textContent = 'Tick one or more relevant styles. Categories are still required; styles only prioritize suggestions.'; }
    }

    compensationType.addEventListener('change', showCompensationFields);
    eventPhotos.addEventListener('change', showSelectedPhotos);
    eventVideos.addEventListener('change', showSelectedVideos);
    categoryCheckboxes.forEach(function (checkbox) { checkbox.addEventListener('change', showRelevantGenres); });
    firstPrize.addEventListener('input', checkPrizeOrder);
    secondPrize.addEventListener('input', checkPrizeOrder);
    thirdPrize.addEventListener('input', checkPrizeOrder);
    showCompensationFields();
    showRelevantGenres();

});
</script>
@endsection
