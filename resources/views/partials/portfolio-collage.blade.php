@props([
    'items',
    'editable' => false,
    'categories' => null,
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
    $visible = $items;

    if ($count > 4) {
        $visible = $items->take(4);
    }

    $eventName = $items->first()->event_name;
    $caption = $items->first()->caption;
    $performanceTypes = $items->first()->performanceTypeNames();
    $imageCaption = $eventName ?: ($caption ?? '');
    $hasMore = $count > 4;
    $modalId = 'portfolio-gallery-'.$items->first()->id;
    $editModalId = 'portfolio-edit-'.$items->first()->id;
    $categories = $categories ?? ($editable ? \App\Models\Category::where('is_active', true)->orderBy('name')->get() : collect());
@endphp

<article class="portfolio-feed-card">
    <div
        class="portfolio-preview-collage portfolio-feed-collage {{ $layout }}"
        role="button"
        tabindex="0"
        data-bs-toggle="modal"
        data-bs-target="#{{ $modalId }}"
        aria-label="View {{ $eventName ?: 'work sample' }}"
    >
        @foreach($visible as $index => $item)
            <div class="portfolio-collage-tile">
                @if($item->type === 'photo')
                    <img src="{{ $item->fileUrl() }}" alt="{{ $imageCaption }}" loading="lazy" decoding="async">
                @else
                    <video src="{{ $item->fileUrl() }}" muted playsinline preload="none"></video>
                    <span class="portfolio-collage-badge"><i class="fas fa-play me-1"></i>Video</span>
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
                    <h5 class="modal-title">{{ $eventName ?: 'Work sample' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="portfolio-lightbox">
                        @foreach($items as $item)
                            <div class="portfolio-lightbox-item">
                                @if($item->type === 'photo')
                                    <img data-src="{{ $item->fileUrl() }}" alt="{{ $imageCaption }}" loading="lazy" decoding="async">
                                @else
                                    <video data-src="{{ $item->fileUrl() }}" controls playsinline preload="none"></video>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- singer badges -->
                    @if($performanceTypes)
                       <div class="portfolio-type-badges mt-3 mb-2">
                            @foreach($performanceTypes as $typeName)
                                <span class="portfolio-type-badge">{{ $typeName }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if($caption)
                        <p class="mt-3 mb-0">{{ $caption }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($eventName || $caption || $editable || $performanceTypes)
        <div class="portfolio-feed-footer px-3 py-3">
            @if($performanceTypes)
                <div class="portfolio-type-badges @if($eventName || $caption) mb-2 @endif">
                    @foreach($performanceTypes as $typeName)
                        <span class="portfolio-type-badge">{{ $typeName }}</span>
                    @endforeach
                </div>
            @endif
            @if($eventName)
                <h6 class="portfolio-feed-event-name mb-1">{{ $eventName }}</h6>
            @endif
            @if($caption)
                <p class="mb-0 small">{{ $caption }}</p>
            @endif
            @if($editable)
                <div @if($eventName || $caption || $performanceTypes) class="mt-2" @endif>
                    <button type="button" class="btn btn-sm ph-btn-outline" data-bs-toggle="modal" data-bs-target="#{{ $editModalId }}">
                        <i class="fas fa-pen me-1"></i> Edit
                    </button>
                </div>
            @endif
        </div>
    @endif

    @if($editable)
        @php
            $deleteFormId = 'portfolio-delete-'.$items->first()->id;
            $editFormId = 'portfolio-edit-form-'.$items->first()->id;
        @endphp
        <div class="modal fade" id="{{ $editModalId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <form
                    id="{{ $editFormId }}"
                    action="{{ route('performer.portfolio.update') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="modal-content portfolio-edit-form"
                >
                    @csrf
                    @foreach($items as $item)
                        <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                    @endforeach
                    <div class="modal-header">
                        <h5 class="modal-title">Edit post</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                            @include('partials.portfolio-category-select', [
                                'categories' => $categories,
                                'selectedCategoryIds' => $items->first()->categoryIdList(),
                                'inputIdPrefix' => 'portfolio-edit-cat-'.$items->first()->id,
                            ])

                            <label class="form-label text-muted small" for="portfolioEditEventName-{{ $items->first()->id }}">Event Name <span class="text-muted">(optional)</span></label>
                            <input
                                type="text"
                                name="event_name"
                                id="portfolioEditEventName-{{ $items->first()->id }}"
                                class="form-control ph-input mb-3"
                                maxlength="150"
                                placeholder="e.g. Wedding Reception, Corporate Gala…"
                                value="{{ $eventName }}"
                            >

                            <label class="form-label text-muted small" for="portfolioEditCaption-{{ $items->first()->id }}">Caption</label>
                            <textarea name="caption" id="portfolioEditCaption-{{ $items->first()->id }}" class="form-control ph-input mb-3" rows="3" maxlength="2000">{{ $caption }}</textarea>

                            
                            <div class="portfolio-edit-grid mb-3">
                                @foreach($items as $item)
                                    <div class="portfolio-edit-tile" data-item-id="{{ $item->id }}">
                                        @if($item->type === 'photo')
                                            <img data-src="{{ $item->fileUrl() }}" alt="" loading="lazy" decoding="async">
                                        @else
                                            <video data-src="{{ $item->fileUrl() }}" muted playsinline preload="none"></video>
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
                                accept="image/jpeg,image/png,video/mp4,video/quicktime"
                                multiple
                            >
                        </div>
                        <div class="modal-footer d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <button
                                type="submit"
                                form="{{ $deleteFormId }}"
                                class="btn ph-btn-outline portfolio-delete-btn"
                            >
                                Delete
                            </button>
                            <div class="d-flex gap-2 ms-auto">
                                <button type="button" class="btn ph-btn-outline portfolio-edit-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn ph-btn-primary portfolio-save-btn">Save changes</button>
                            </div>
                        </div>
                </form>
                <form
                    id="{{ $deleteFormId }}"
                    action="{{ route('performer.portfolio.destroy-batch') }}"
                    method="POST"
                    class="d-none portfolio-delete-form"
                >
                    @csrf
                    @method('DELETE')
                    @foreach($items as $item)
                        <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                    @endforeach
                </form>
            </div>
        </div>
    @endif
</article>

@once
    @push('scripts')
        <script>
        document.addEventListener('show.bs.modal', (e) => {
            e.target.querySelectorAll('[data-src]').forEach((el) => {
                el.src = el.dataset.src;
                el.removeAttribute('data-src');
                if (el.tagName === 'VIDEO') {
                    el.load();
                }
            });
        });

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
            if (e.target.matches('.portfolio-edit-form')) {
                const btn = e.target.querySelector('.portfolio-save-btn');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Saving…';
                }
                return;
            }

            if (!e.target.matches('.portfolio-delete-form')) return;

            e.preventDefault(); // stop delete until they confirm in the modal

            const confirmModalEl = document.getElementById('portfolioDeleteConfirmModal');
            const confirmBtn = document.getElementById('portfolioConfirmDeleteBtn');
            if (!confirmModalEl || !confirmBtn || typeof bootstrap === 'undefined') return;

            window.__pendingPortfolioDeleteForm = e.target;

            // Close Edit first so the confirm modal is alone and readable.
            const editModal = e.target.closest('.modal');
            const showConfirm = () => {
                bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
            };

            if (editModal && editModal.classList.contains('show')) {
                editModal.addEventListener('hidden.bs.modal', showConfirm, { once: true });
                bootstrap.Modal.getInstance(editModal)?.hide();
            } else {
                showConfirm();
            }
        });

        document.getElementById('portfolioConfirmDeleteBtn')?.addEventListener('click', () => {
            const form = window.__pendingPortfolioDeleteForm;
            if (!form) return;

            const btn = document.querySelector(`button.portfolio-delete-btn[form="${form.id}"]`);
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Deleting…';
            }

            bootstrap.Modal.getInstance(document.getElementById('portfolioDeleteConfirmModal'))?.hide();
            window.__pendingPortfolioDeleteForm = null;
            form.submit();
        });
        </script>
    @endpush
@endonce
