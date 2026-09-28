@props(['photos', 'title' => ''])

@php
    $count = $photos->count();
    $layout = 'portfolio-collage--many';

    if ($count === 1) {
        $layout = 'portfolio-collage--1';
    } elseif ($count === 2) {
        $layout = 'portfolio-collage--2';
    } elseif ($count === 3) {
        $layout = 'portfolio-collage--3';
    } elseif ($count === 4) {
        $layout = 'portfolio-collage--4';
    }
    $visible = $photos;

    if ($count > 4) {
        $visible = $photos->take(4);
    }
    $hasMore = $count > 4;
    $modalId = 'event-gallery-'.$photos->first()->id;
@endphp

<div
    class="portfolio-preview-collage portfolio-feed-collage event-feed-collage {{ $layout }}"
    role="button"
    tabindex="0"
    data-bs-toggle="modal"
    data-bs-target="#{{ $modalId }}"
    aria-label="View all {{ $count }} event media items"
>
    @foreach($visible as $index => $photo)
        <div class="portfolio-collage-tile event-feed-collage-tile">
            @if($photo->isVideo())
                <video muted playsinline preload="metadata"><source src="{{ $photo->fileUrl() }}"></video>
                <span class="portfolio-collage-badge"><i class="fas fa-play me-1"></i>Video</span>
            @else
                <img src="{{ $photo->fileUrl() }}" alt="{{ $title }}" loading="lazy">
            @endif
            @if($hasMore && $index === 3)
                <div class="portfolio-collage-more">+{{ $count - 4 }}</div>
            @endif
        </div>
    @endforeach
</div>

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }} Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="portfolio-lightbox">
                    @foreach($photos as $photo)
                        <div class="portfolio-lightbox-item">
                            @if($photo->isVideo())
                                <video controls playsinline preload="metadata"><source src="{{ $photo->fileUrl() }}"></video>
                            @else
                                <img src="{{ $photo->fileUrl() }}" alt="{{ $title }}">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
