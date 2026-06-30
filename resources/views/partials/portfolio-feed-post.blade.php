@props([
    'items',
    'performer',
    'editable' => false,
    'isOwn' => false,
])

@php
    $first = $items->first();
    $postedAt = $first->created_at;
    $photoUrl = $performer->profile_photo
        ? asset('storage/'.$performer->profile_photo)
        : 'https://ui-avatars.com/api/?name='.urlencode($performer->stage_name).'&background=6346ff&color=fff&size=128';
@endphp

<article class="portfolio-feed-post ph-card overflow-hidden">
    <div class="portfolio-feed-post-header p-3 d-flex align-items-center gap-3">
        <a href="{{ route('talent.show', $performer) }}" class="flex-shrink-0">
            <img src="{{ $photoUrl }}" alt="" class="rounded-circle portfolio-feed-avatar" width="44" height="44">
        </a>
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('talent.show', $performer) }}" class="text-white text-decoration-none fw-semibold mb-0">
                    {{ $performer->stage_name }}
                </a>
                @if($isOwn)
                    <span class="badge rounded-pill" style="background: rgba(99, 70, 255, 0.2); color: #c4b5fd;">Your post</span>
                @endif
            </div>
            <small class="text-muted">
                {{ $performer->category?->name ?? 'Performer' }}
                · {{ $postedAt->diffForHumans() }}
            </small>
        </div>
    </div>

    @include('partials.portfolio-collage', ['items' => $items, 'editable' => $editable])

    @unless($editable)
        <div class="portfolio-feed-post-actions p-3 pt-0 d-flex flex-wrap gap-2">
            <a href="{{ route('talent.show', $performer) }}" class="btn btn-sm ph-btn-outline">View Profile</a>

            @if(auth()->user()->isOrganizer() && ! $isOwn)
                @if(auth()->user()->hasLimitedAccess())
                    <a href="{{ auth()->user()->onboardingRoute() }}" class="btn btn-sm ph-btn-primary">
                        <i class="fas fa-lock me-1"></i> Complete sign-up to book
                    </a>
                @else
                    <a href="{{ route('organizer.bookings.create', $performer) }}" class="btn btn-sm ph-btn-primary">
                        Send Booking Request
                    </a>
                @endif
            @endif

            @if(! $isOwn && auth()->id() !== $performer->user_id)
                <a href="{{ route('messages.show', $performer->user) }}" class="btn btn-sm ph-btn-outline">
                    <i class="fas fa-envelope me-1"></i> Message
                </a>
            @endif
        </div>
    @endunless
</article>
