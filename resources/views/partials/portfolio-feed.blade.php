@props([
    'posts',
    'emptyMessage' => 'No posts yet. Upload photos or videos to share your work.',
    'ownProfileId' => null,
])

<div class="portfolio-feed-stream">
    @forelse($posts as $items)
        @php $performer = $items->first()->performerProfile; @endphp
        @include('partials.portfolio-feed-post', [
            'items' => $items,
            'performer' => $performer,
            'isOwn' => $ownProfileId && $performer->id === $ownProfileId,
        ])
    @empty
        <div class="ph-card p-4 text-center text-muted">{{ $emptyMessage }}</div>
    @endforelse
</div>
