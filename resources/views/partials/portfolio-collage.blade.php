@props([
    'items',
    'editable' => false,
])

@php
    $count = $items->count();
    $layout = match (true) {
        $count === 1 => 'portfolio-collage--1',
        $count === 2 => 'portfolio-collage--2',
        $count === 3 => 'portfolio-collage--3',
        $count === 4 => 'portfolio-collage--4',
        default => 'portfolio-collage--many',
    };
    $visible = $count > 4 ? $items->take(4) : $items;
    $caption = $items->first()->caption;
    $hasMore = $count > 4;
    $modalId = 'portfolio-gallery-'.$items->first()->id;
    $editModalId = 'portfolio-edit-'.$items->first()->id;
@endphp

<article class="portfolio-feed-card">
    <div
        class="portfolio-preview-collage portfolio-feed-collage {{ $layout }}"
        @if($hasMore)
            role="button"
            tabindex="0"
            data-bs-toggle="modal"
            data-bs-target="#{{ $modalId }}"
            aria-label="View all {{ $count }} items"
        @endif
    >
        @foreach($visible as $index => $item)
            <div class="portfolio-collage-tile">
                @if($item->type === 'photo')
                    <img src="{{ $item->fileUrl() }}" alt="{{ $caption ?? '' }}">
                @else
                    <video src="{{ $item->fileUrl() }}" @if(!$hasMore) controls @endif playsinline></video>
                    <span class="portfolio-collage-badge"><i class="fas fa-play me-1"></i>Video</span>
                @endif
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
                        <h5 class="modal-title">All {{ $count }} items</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="portfolio-gallery-grid">
                            @foreach($items as $item)
                                <div class="portfolio-gallery-item">
                                    @if($item->type === 'photo')
                                        <img src="{{ $item->fileUrl() }}" alt="{{ $caption ?? '' }}">
                                    @else
                                        <video src="{{ $item->fileUrl() }}" controls playsinline></video>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($caption || $editable)
        <div class="portfolio-feed-footer p-3">
            @if($caption)
                <p class="mb-0 small">{{ $caption }}</p>
            @endif
            @if($editable)
                <div class="{{ $caption ? 'mt-2' : '' }}">
                    <button type="button" class="btn btn-sm ph-btn-outline" data-bs-toggle="modal" data-bs-target="#{{ $editModalId }}">
                        <i class="fas fa-pen me-1"></i> Edit
                    </button>
                </div>
            @endif
        </div>
    @endif

    @if($editable)
        <div class="modal fade" id="{{ $editModalId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="{{ route('performer.portfolio.update') }}" method="POST" enctype="multipart/form-data" class="portfolio-edit-form">
                        @csrf
                        @foreach($items as $item)
                            <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                        @endforeach
                        <div class="modal-header">
                            <h5 class="modal-title">Edit post</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label text-muted small" for="portfolioEditCaption-{{ $items->first()->id }}">Caption</label>
                            <textarea name="caption" id="portfolioEditCaption-{{ $items->first()->id }}" class="form-control ph-input mb-3" rows="3" maxlength="2000">{{ $caption }}</textarea>

                            <label class="form-label text-muted small">Current photos/videos</label>
                            <p class="text-muted small mb-2">Click the <i class="fas fa-times"></i> on an item to remove it from this post.</p>
                            <div class="portfolio-edit-grid mb-3">
                                @foreach($items as $item)
                                    <div class="portfolio-edit-tile" data-item-id="{{ $item->id }}">
                                        @if($item->type === 'photo')
                                            <img src="{{ $item->fileUrl() }}" alt="">
                                        @else
                                            <video src="{{ $item->fileUrl() }}" muted playsinline></video>
                                        @endif
                                        <button type="button" class="portfolio-edit-tile-remove" aria-label="Remove this item">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            <label for="portfolioEditFiles-{{ $items->first()->id }}" class="form-label text-muted small">Add more photos or videos</label>
                            <input
                                type="file"
                                name="files[]"
                                id="portfolioEditFiles-{{ $items->first()->id }}"
                                class="form-control ph-input"
                                accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime,video/*"
                                multiple
                            >
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn ph-btn-outline" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn ph-btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</article>

@once
    @push('scripts')
        <script>
        document.addEventListener('hidden.bs.modal', (e) => {
            e.target.querySelectorAll('video').forEach(video => video.pause());
        });

        document.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.portfolio-edit-tile-remove');
            if (!removeBtn) return;

            const tile = removeBtn.closest('.portfolio-edit-tile');
            const form = removeBtn.closest('form');
            const itemId = tile.dataset.itemId;
            const existing = form.querySelector(`input[name="remove_ids[]"][value="${itemId}"]`);

            if (existing) {
                existing.remove();
                tile.classList.remove('portfolio-edit-tile--removed');
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            } else {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'remove_ids[]';
                hidden.value = itemId;
                form.appendChild(hidden);
                tile.classList.add('portfolio-edit-tile--removed');
                removeBtn.innerHTML = '<i class="fas fa-undo"></i>';
            }
        });

        document.addEventListener('submit', (e) => {
            if (!e.target.matches('.portfolio-edit-form')) return;
            const btn = e.target.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Saving…';
            }
        });
        </script>
    @endpush
@endonce
