@extends('layouts.app')

@section('title', 'Portfolio')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Portfolio</h2>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-semibold mb-1">Upload Photos or Videos</h5>
    <p class="text-muted small mb-3">Select multiple files — they’ll preview here like a post before you upload.</p>
    <form method="POST" action="{{ route('performer.portfolio.store') }}" enctype="multipart/form-data" id="portfolioUploadForm">
        @csrf
        <div class="portfolio-upload-zone mb-3">
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
                <i class="fas fa-images fa-2x mb-2"></i>
                <span class="fw-semibold">Add photos or videos</span>
                <span class="small text-muted">Click to browse · JPG, PNG, WEBP, GIF, MP4, WEBM · max 50 MB each</span>
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
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label text-muted small" for="portfolioCaption">Caption</label>
                <input type="text" name="caption" id="portfolioCaption" class="form-control ph-input" placeholder="e.g. Live set at Sinulog 2026" value="{{ old('caption') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn ph-btn-primary w-100" id="portfolioSubmitBtn" disabled>Upload</button>
            </div>
        </div>
    </form>
</div>

<div class="portfolio-feed-list mb-4">
    @forelse($portfolioGroups as $group)
        <div class="ph-card overflow-hidden mb-3">
            @include('partials.portfolio-feed-post', [
                'items' => $group,
                'performer' => auth()->user()->performerProfile,
                'editable' => true,
                'isOwn' => true,
            ])
        </div>
    @empty
        <p class="text-muted mb-0">No portfolio items yet.</p>
    @endforelse
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
