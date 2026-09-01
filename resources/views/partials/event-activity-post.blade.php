@props(['event'])

@php
    $organizer = $event->organizer;
    $profile = null;
    $name = 'Organizer';
    $photoUrl = null;

    if ($organizer) {
        $profile = $organizer->organizerProfile;
    }

    if ($organizer && $organizer->name) {
        $name = $organizer->name;
    }

    if ($profile && $profile->organization_name) {
        $name = $profile->organization_name;
    }

    if ($profile && $profile->profilePhotoUrl()) {
        $photoUrl = $profile->profilePhotoUrl();
    }

    if (! $photoUrl) {
        $photoUrl = 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=6346ff&color=fff&size=128';
    }
@endphp

<article class="event-feed-post mb-3">
    <div class="event-feed-post-header">
        <img src="{{ $photoUrl }}" alt="" class="rounded-circle event-feed-avatar" width="44" height="44">
        <div>
            <strong class="event-card-organizer">{{ $name }}</strong>
            <small class="text-muted d-block">Created a new event · {{ $event->created_at->diffForHumans() }}</small>
        </div>
    </div>

    <div class="event-feed-body">
        <h5 class="event-card-title event-feed-title">{{ $event->title }}</h5>

        @if($event->description)
            <p class="text-muted event-feed-description">{{ $event->description }}</p>
        @endif

        <p class="event-feed-meta mb-0">
            <span>{{ \Illuminate\Support\Carbon::parse($event->event_date)->format('M j, Y') }}</span>
            <span>{{ $event->venue }}</span>
            @if($event->budget)
                <span>₱{{ number_format((float) $event->budget, 0) }}</span>
            @endif
        </p>
    </div>

    @if($event->photos->count() > 1)
        @include('partials.event-photo-collage', ['photos' => $event->photos, 'title' => $event->title])
    @elseif($event->photos->count() === 1)
        <div class="event-feed-cover">
            <img src="{{ $event->photos->first()->fileUrl() }}" alt="{{ $event->title }}" loading="lazy">
        </div>
    @elseif($event->coverPhotoUrl())
        <div class="event-feed-cover">
            <img src="{{ $event->coverPhotoUrl() }}" alt="{{ $event->title }}" loading="lazy">
        </div>
    @endif

    <div class="portfolio-feed-post-actions p-3 pt-3">
        <a href="{{ route('organizer.events.show', $event) }}" class="btn btn-sm ph-btn-outline">View Event</a>
    </div>
</article>
