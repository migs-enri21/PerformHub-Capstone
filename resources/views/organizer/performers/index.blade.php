@extends('layouts.app')

@section('title', 'Find Performers')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@php
    $eventTypeName = 'N/A';

    if ($selectedEvent && $selectedEvent->eventType) {
        $eventTypeName = $selectedEvent->eventType->name;
    }
@endphp

<div class="organizer-performer-search-header mb-4">
    <div>
        <span class="organizer-page-kicker">Talent Directory</span>
        <h2 class="fw-bold mb-1">Find Performers</h2>
        <p class="text-muted mb-0">Search verified performers by category, specialty, genre, and availability.</p>
    </div>
</div>

@if($selectedEvent)
    <div class="organizer-selected-event mb-4">
        <i class="fas fa-wand-magic-sparkles"></i>
        <div>
            <strong>Recommended for {{ $selectedEvent->title }}</strong>
            <small>{{ $eventTypeName }} · {{ \Carbon\Carbon::parse($selectedEvent->event_date)->format('F d, Y') }}</small>
        </div>
    </div>
@endif

<div class="organizer-search-filters mb-4">
    <form method="GET" class="row g-3">
        @if($selectedEvent) <input type="hidden" name="event" value="{{ $selectedEvent->id }}">
        @endif

        <div class="col-md-6 col-lg-3"><label class="form-label small fw-semibold" for="performerSearch">Search</label>
            <input type="text" id="performerSearch" name="search" class="form-control ph-input" placeholder="Name, specialty, genre, or location" value="{{ request('search') }}">
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-label small fw-semibold" for="categoryId">Category</label>
            <select name="category_id" id="categoryId" class="form-select ph-input">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 col-lg-2"><label class="form-label small fw-semibold">Specialty</label>
            @include('partials.specialty-select', ['value' => request('specialty'), 'placeholder' => 'All Specialties'])
        </div>
        <div class="col-md-6 col-lg-2">
            <label class="form-label small fw-semibold">Genre</label>
            @include('partials.genre-select', ['value' => request('genre'), 'placeholder' => 'All Genres'])
        </div>
        <div class="col-md-6 col-lg-2"><label class="form-label small fw-semibold" for="availableDate">Available on</label>
            <input type="date" id="availableDate" name="available_date" class="form-control ph-input" value="{{ request('available_date') }}">
        </div>
        <div class="col-md-6 col-lg-1 d-flex align-items-end">
            <button class="btn ph-btn-primary w-100">Search</button>
        </div>
    </form>
</div>

<p class="text-muted small mb-3">{{ $performers->total() }} verified performer(s) found</p>

<div class="row g-4 organizer-performer-results">
    @forelse($performers as $performer)
        <div class="col-md-6 col-lg-4">
            @php
                $location = $performer->shortLocation();
                $rateLines = $performer->rateLines();
            @endphp

            <div class="organizer-performer-card h-100">
                <div class="d-flex align-items-start gap-3 mb-3">
                    @if($performer->profilePhotoUrl())
                        <img src="{{ $performer->profilePhotoUrl() }}" class="performer-avatar" alt="{{ $performer->stage_name }}">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($performer->stage_name) }}&background=6346ff&color=fff" class="performer-avatar" alt="{{ $performer->stage_name }}">
                    @endif

                    <div class="flex-grow-1 min-w-0">
                        <h5 class="mb-1">{{ $performer->stage_name }} @if($performer->is_verified_badge)<i class="fas fa-circle-check verified-badge"></i>@endif</h5>
                        <small class="text-muted d-block"><i class="fas fa-location-dot me-1"></i>{{ $location }}</small>
                    </div>
                </div>

                <div class="organizer-performer-tags mb-3">
                    @foreach($performer->categories as $category)
                        <span>{{ $category->name }}</span>
                    @endforeach
                    @foreach($performer->specialtyList() as $specialty)
                        <span>{{ $specialty }}</span>
                    @endforeach
                    @foreach($performer->genreList() as $genre)
                        <span>{{ $genre }}</span>
                    @endforeach
                </div>

                <p class="text-muted small mb-3">{{ Str::limit($performer->bio, 105) }}</p>

                @if($rateLines)
                    <p class="small mb-3"><strong>Rate:</strong> {{ implode(' · ', $rateLines) }}</p>
                @endif

                <div class="d-flex gap-2 mt-auto">
                    <a href="{{ route('organizer.performers.show', $performer) }}" class="btn ph-btn-outline btn-sm flex-fill">View Profile</a>
                    <a href="{{ route('organizer.bookings.create', ['performer' => $performer, 'event' => request('event')]) }}" class="btn ph-btn-primary btn-sm flex-fill">Book</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="organizer-performers-empty">No verified performers match your filters yet.</div></div>
    @endforelse
</div>

{{ $performers->links() }}
@endsection
