@extends('layouts.app')

@section('title', 'Portfolio')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
@php
    $sampleCount = $portfolioGroups->count();

    if ($sampleCount === 1) {
        $sampleLabel = '1 post';
    } else {
        $sampleLabel = $sampleCount.' posts';
    }
@endphp

<div class="portfolio-page">
    @include('partials.performer-profile-header', [
        'performer' => $profile,
        'editable' => true,
    ])

    <div class="portfolio-gallery-section">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="fw-semibold mb-0">Work Samples</h5>
                <p class="text-muted small mb-0">{{ $sampleLabel }} · Member since {{ auth()->user()->created_at->format('Y') }}</p>
            </div>
            <button type="button" class="btn ph-btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addWorkSampleModal">
                <i class="fas fa-plus me-1"></i> Add Work Sample
            </button>
        </div>

        <div class="portfolio-gallery-list">
            @forelse($portfolioGroups as $group)
                @include('partials.portfolio-collage', [
                    'items' => $group,
                    'editable' => true,
                ])
            @empty
                <div class="portfolio-feed-empty text-muted">No work samples yet. Click "Add Work Sample" to upload your first photo or video.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="addWorkSampleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('performer.portfolio.store') }}" enctype="multipart/form-data" id="portfolioUploadForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Work Sample</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="portfolio-sketch-field">
                        <label class="portfolio-sketch-label" for="portfolioEventName">Event Name <span class="text-muted fw-normal">(optional)</span></label>
                        <input
                            type="text"
                            name="event_name"
                            id="portfolioEventName"
                            class="form-control ph-input"
                            maxlength="150"
                            placeholder="e.g. Wedding Reception, Corporate Gala…"
                            value="{{ old('event_name') }}"
                        >
                    </div>

                    <div class="portfolio-sketch-field">
                        <label class="portfolio-sketch-label" for="portfolioCaption">Caption</label>
                        <textarea
                            name="caption"
                            id="portfolioCaption"
                            class="form-control ph-input portfolio-sketch-input"
                            rows="2"
                            maxlength="2000"
                            placeholder="Describe this performance…"
                        >{{ old('caption') }}</textarea>
                    </div>

                    <div class="portfolio-sketch-field mb-0">
                        <label class="portfolio-sketch-label" for="portfolioFiles">Photos or Videos</label>
                        <div class="portfolio-upload-zone">
                            <input
                                type="file"
                                name="files[]"
                                id="portfolioFiles"
                                class="portfolio-file-input"
                                accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime,video/*"
                                multiple
                                required
                            >
                            <label for="portfolioFiles" class="portfolio-upload-trigger" id="portfolioUploadTrigger">
                                <span class="portfolio-sketch-plus"><i class="fas fa-plus"></i></span>
                                <span class="fw-semibold">Add photos or videos</span>
                                <span class="small text-muted">JPG, PNG, WEBP, GIF, MP4, WEBM · max 500 MB each</span>
                            </label>
                            <div class="portfolio-preview-collage d-none" id="portfolioPreviewCollage" aria-live="polite"></div>
                            <label for="portfolioFiles" class="portfolio-collage-add d-none" id="portfolioAddMore">
                                <i class="fas fa-plus"></i><span>Add more</span>
                            </label>
                            <div class="d-flex justify-content-between align-items-center mt-2 d-none" id="portfolioPreviewActions">
                                <span class="text-muted small" id="portfolioFileCount"></span>
                                <button type="button" class="btn btn-sm ph-btn-outline" id="portfolioClearFiles">Clear all</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn ph-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn ph-btn-primary" id="portfolioSubmitBtn" disabled>Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const input = document.getElementById('portfolioFiles');
    const trigger = document.getElementById('portfolioUploadTrigger');
    const collage = document.getElementById('portfolioPreviewCollage');
    const actions = document.getElementById('portfolioPreviewActions');
    const fileCount = document.getElementById('portfolioFileCount');
    const clearBtn = document.getElementById('portfolioClearFiles');
    const submitBtn = document.getElementById('portfolioSubmitBtn');
    const addMore = document.getElementById('portfolioAddMore');

    if (!input || !collage) {
        return;
    }

    let selectedFiles = [];
    let objectUrls = [];

    function revokeUrls() {
        objectUrls.forEach(url => URL.revokeObjectURL(url));
        objectUrls = [];
    }

    function syncInputFiles() {
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        input.files = dataTransfer.files;
        submitBtn.disabled = selectedFiles.length === 0;
    }

    function collageLayoutClass(count) {
        if (count === 1) return 'portfolio-collage--1';
        if (count === 2) return 'portfolio-collage--2';
        if (count === 3) return 'portfolio-collage--3';
        if (count === 4) return 'portfolio-collage--4';
        return 'portfolio-collage--many';
    }

    function renderPreview() {
        revokeUrls();

        if (selectedFiles.length === 0) {
            collage.innerHTML = '';
            collage.classList.add('d-none');
            trigger.classList.remove('d-none');
            actions.classList.add('d-none');
            addMore?.classList.add('d-none');
            syncInputFiles();
            return;
        }

        trigger.classList.add('d-none');
        collage.classList.remove('d-none');
        actions.classList.remove('d-none');
        addMore?.classList.remove('d-none');
        collage.className = `portfolio-preview-collage ${collageLayoutClass(selectedFiles.length)}`;
        fileCount.textContent = `${selectedFiles.length} file${selectedFiles.length === 1 ? '' : 's'} selected`;

        collage.innerHTML = '';

        const visibleFiles = selectedFiles.length > 4 ? selectedFiles.slice(0, 4) : selectedFiles;

        visibleFiles.forEach((file, index) => {
            const tile = document.createElement('div');
            tile.className = 'portfolio-collage-tile';

            const isVideo = file.type.startsWith('video/');
            const url = URL.createObjectURL(file);
            objectUrls.push(url);

            if (isVideo) {
                const video = document.createElement('video');
                video.src = url;
                video.muted = true;
                video.playsInline = true;
                tile.appendChild(video);
                const badge = document.createElement('span');
                badge.className = 'portfolio-collage-badge';
                badge.innerHTML = '<i class="fas fa-play me-1"></i>Video';
                tile.appendChild(badge);
            } else {
                const img = document.createElement('img');
                img.src = url;
                img.alt = '';
                tile.appendChild(img);
            }

            if (selectedFiles.length > 4 && index === 3) {
                const more = document.createElement('div');
                more.className = 'portfolio-collage-more';
                more.textContent = `+${selectedFiles.length - 4}`;
                tile.appendChild(more);
            }

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'portfolio-collage-remove';
            removeBtn.setAttribute('aria-label', 'Remove file');
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const actualIndex = selectedFiles.indexOf(file);
                if (actualIndex !== -1) {
                    selectedFiles.splice(actualIndex, 1);
                }
                renderPreview();
            });
            tile.appendChild(removeBtn);

            collage.appendChild(tile);
        });

        syncInputFiles();
    }

    input.addEventListener('change', () => {
        const incoming = Array.from(input.files || []);
        const merged = [...selectedFiles];

        incoming.forEach(file => {
            const duplicate = merged.some(existing =>
                existing.name === file.name &&
                existing.size === file.size &&
                existing.lastModified === file.lastModified
            );
            if (!duplicate) {
                merged.push(file);
            }
        });

        selectedFiles = merged;
        renderPreview();
    });

    clearBtn?.addEventListener('click', () => {
        selectedFiles = [];
        renderPreview();
    });
})();
</script>
@endpush
