@props(['event', 'hasApplied' => false])

@php
    $organizer = $event->organizer;
    $orgProfile = $organizer?->organizerProfile;
    $orgName = $orgProfile?->organization_name ?: ($organizer?->name ?? 'Unknown Organizer');
    $photoUrl = $orgProfile?->profilePhotoUrl()
        ?? 'https://ui-avatars.com/api/?name='.urlencode($orgName).'&background=6346ff&color=fff&size=128';
    $eventDate = $event->event_date ? \Illuminate\Support\Carbon::parse($event->event_date) : null;
    $galleryPhotos = $event->photos;
    $photoCount = $galleryPhotos->count();
@endphp

<article class="event-feed-post">
    <div class="event-feed-post-header">
        <img src="{{ $photoUrl }}" alt="" class="rounded-circle event-feed-avatar flex-shrink-0" width="40" height="40">
        <div class="flex-grow-1 min-w-0">
            <p class="text-white fw-semibold mb-0 text-truncate">{{ $orgName }}</p>
            <small class="text-muted">{{ $event->created_at?->diffForHumans() ?? 'Recently' }} · {{ $event->status }}</small>
        </div>
    </div>

    <div class="event-feed-body">
        <h5 class="text-white fw-bold mb-2 event-feed-title">{{ $event->title }}</h5>

        @if($event->description)
            <p class="text-muted mb-2 event-feed-description">{{ $event->description }}</p>
        @endif

        <p class="event-feed-meta mb-0">
            @if($eventDate)
                <span>{{ $eventDate->format('M j, Y') }}@if($event->start_time) · {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}@endif</span>
            @endif
            @if($event->venue)
                <span>{{ $event->venue }}</span>
            @endif
            @if($event->preferredCategory)
                <span>{{ $event->preferredCategory->name }}</span>
            @endif
            @if($event->budget)
                <span>₱{{ number_format((float) $event->budget, 0) }}</span>
            @endif
        </p>
    </div>

    @if($photoCount > 1)
        @include('partials.event-photo-collage', ['photos' => $galleryPhotos, 'title' => $event->title])
    @elseif($photoCount === 1)
        <div class="event-feed-cover">
            <img src="{{ $galleryPhotos->first()->fileUrl() }}" alt="{{ $event->title }}" loading="lazy">
        </div>
    @elseif($event->coverPhotoUrl())
        <div class="event-feed-cover">
            <img src="{{ $event->coverPhotoUrl() }}" alt="{{ $event->title }}" loading="lazy">
        </div>
    @else
        <div class="event-feed-cover event-feed-cover--empty">
            <div class="event-feed-cover-placeholder" aria-hidden="true">
                <i class="fas fa-image"></i>
            </div>
        </div>
    @endif

    <div class="event-feed-footer">
        @if($hasApplied)
            <button type="button" class="event-feed-footer-btn event-feed-footer-btn--applied w-100" disabled>
                <i class="fas fa-check me-1"></i>Applied
            </button>
        @elseif(auth()->user()->hasLimitedAccess())
            <a href="{{ auth()->user()->onboardingRoute() }}" class="event-feed-footer-btn w-100">
                <i class="fas fa-lock me-1"></i>Sign up
            </a>
        @else
            <form method="POST" action="{{ route('performer.events.apply', $event) }}" class="m-0 w-100">
                @csrf
                <button type="submit" class="event-feed-footer-btn w-100">
                    Apply
                </button>
            </form>
        @endif
    </div>
</article>
