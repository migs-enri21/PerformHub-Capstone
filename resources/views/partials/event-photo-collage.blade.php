@props(['photos', 'title' => ''])

@php
    $count = $photos->count();
    $layout = match (true) {
        $count === 1 => 'portfolio-collage--1',
        $count === 2 => 'portfolio-collage--2',
        $count === 3 => 'portfolio-collage--3',
        $count === 4 => 'portfolio-collage--4',
        default => 'portfolio-collage--many',
    };
    $visible = $count > 4 ? $photos->take(4) : $photos;
    $hasMore = $count > 4;
    $modalId = 'event-gallery-'.$photos->first()->id;
@endphp

<div
    class="portfolio-preview-collage portfolio-feed-collage event-feed-collage {{ $layout }}"
    @if($hasMore)
        role="button"
        tabindex="0"
        data-bs-toggle="modal"
        data-bs-target="#{{ $modalId }}"
        aria-label="View all {{ $count }} photos"
    @endif
>
    @foreach($visible as $index => $photo)
        <div class="portfolio-collage-tile event-feed-collage-tile">
            <img src="{{ $photo->fileUrl() }}" alt="{{ $title }}" loading="lazy">
            @if($hasMore && $index === 3)
                <div class="portfolio-collage-more">+{{ $count - 4 }}</div>
            @endif
        </div>
    @endforeach
</div>

@if($hasMore)
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All {{ $count }} photos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="portfolio-gallery-grid">
                        @foreach($photos as $photo)
                            <div class="portfolio-gallery-item">
                                <img src="{{ $photo->fileUrl() }}" alt="{{ $title }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
