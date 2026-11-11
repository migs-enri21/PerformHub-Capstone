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

    $postedDate = $event->created_at->format('F j, Y');

    if ($event->created_at->greaterThanOrEqualTo(now()->subWeeks(2))) {
        $postedDate = $event->created_at->diffForHumans();
    }
@endphp

<article class="event-feed-post organizer-event-activity-post mb-3">
    <div class="event-feed-post-header">
        <img src="{{ $photoUrl }}" alt="" class="rounded-circle event-feed-avatar" width="44" height="44">
        <div>
            <strong class="event-card-organizer">{{ $name }}</strong>
            <small class="text-muted d-block">Created a new event · {{ $postedDate }}</small>
        </div>
    </div>

    <div class="event-feed-body">
        <h5 class="event-card-title event-feed-title">{{ $event->title }}</h5>

        @if($event->description)
            @if(strlen($event->description) > 280)
                <p class="text-muted event-feed-description mb-1">{{ Str::limit($event->description, 280) }}</p>
                <p class="text-muted event-feed-description mb-1 d-none" data-full-description>{{ $event->description }}</p>
                <button type="button" class="event-description-toggle" data-description-toggle>See more</button>
            @else
                <p class="text-muted event-feed-description">{{ $event->description }}</p>
            @endif
        @endif

        <p class="event-feed-meta mb-0">
            <span>{{ $event->dateRangeLabel() }}</span>
            <span>{{ $event->venue }}</span>
            @if($event->budget)
                <span>₱{{ number_format((float) $event->budget, 0) }}</span>
            @endif
            @if($event->compensation_type === 'hourly' && $event->rate_per_hour)
                <span>Rate per hour: PHP {{ number_format((float) $event->rate_per_hour, 0) }}</span>
                <span>{{ $event->work_hours_per_day }} work hours per day</span>
            @endif
            @if($event->compensation_type === 'contest')
                <span>Contest prizes available</span>
            @endif
        </p>
    </div>

    @if($event->photos->count() > 1)
        @include('partials.event-photo-collage', ['photos' => $event->photos, 'title' => $event->title])
    @elseif($event->photos->count() === 1)
        <div class="event-feed-cover">
            @if($event->photos->first()->isVideo())
                <video controls preload="metadata"><source src="{{ $event->photos->first()->fileUrl() }}"></video>
            @else
                <img src="{{ $event->photos->first()->fileUrl() }}" alt="{{ $event->title }}" loading="lazy">
            @endif
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

@once
    @push('scripts')
    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-description-toggle]');

            if (!button) {
                return;
            }

            const post = button.closest('.event-feed-body');
            const descriptions = post.querySelectorAll('.event-feed-description');

            descriptions.forEach(function (description) {
                description.classList.toggle('d-none');
            });

            if (button.textContent === 'See more') {
                button.textContent = 'See less';
            } else {
                button.textContent = 'See more';
            }
        });
    </script>
    @endpush
@endonce
