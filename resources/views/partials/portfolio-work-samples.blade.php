@props([
    'groups',
    'memberSince' => null,
    'emptyMessage' => 'No work samples yet.',
])

@php
    $sampleCount = $groups->count();

    if ($sampleCount === 1) {
        $sampleLabel = '1 post';
    } else {
        $sampleLabel = $sampleCount.' posts';
    }
@endphp

<div class="portfolio-gallery-section">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h5 class="fw-semibold mb-0">Work Samples</h5>
            <p class="text-muted small mb-0">
                {{ $sampleLabel }}
                @if($memberSince)
                    · Member since {{ $memberSince->format('Y') }}
                @endif
            </p>
        </div>
        {{ $actions ?? '' }}
    </div>

    <div class="portfolio-gallery-list">
        @forelse($groups as $group)
            @include('partials.portfolio-collage', [
                'items' => $group,
                'editable' => false,
            ])
        @empty
            <div class="portfolio-feed-empty text-muted">{{ $emptyMessage }}</div>
        @endforelse
    </div>
</div>
