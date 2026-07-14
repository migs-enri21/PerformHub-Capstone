@extends('layouts.app')

@section('title', 'Organizer Profile')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Organizer Profile</h2>
<form method="POST" action="{{ route('organizer.profile.update') }}" enctype="multipart/form-data" id="profileForm">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="ph-card p-4 profile-photo-card">
                @include('partials.profile-photo-upload', [
                    'currentUrl' => $profile->profilePhotoUrl(),
                    'fallbackName' => $profile->organization_name,
                ])
            </div>
            <div class="ph-card p-4 mt-4" id="banner">
                <h5 class="fw-semibold mb-3">Banner Image</h5>
                @php
                    $bannerPositionY = old('banner_position_y', $profile->banner_position_y);
                @endphp
                <div
                    class="banner-reposition rounded mb-2 {{ $profile->bannerPhotoUrl() ? '' : 'banner-reposition--empty' }}"
                    id="bannerReposition"
                    style="{{ $profile->bannerPhotoUrl() ? "background-image: url('".$profile->bannerPhotoUrl()."');" : '' }} background-position: center {{ $bannerPositionY }}%;"
                >
                    @unless($profile->bannerPhotoUrl())
                        <span class="banner-reposition-placeholder">No banner uploaded yet</span>
                    @endunless
                    <span class="banner-reposition-hint d-none"><i class="fas fa-arrows-up-down me-1"></i>Drag to reposition</span>
                </div>
                <input type="hidden" name="banner_position_y" id="bannerPositionYInput" value="{{ $bannerPositionY }}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label text-muted small mb-0" for="banner_photo_input">Drag the picture to your preference, or upload a new one (JPG, PNG, WEBP · max 5 MB)</label>
                <button type="button" class="btn ph-btn-outline btn-sm flex-shrink-0 text-nowrap" id="bannerResetPosition">Reset to Center</button>
                </div>
                <input type="file" name="banner_photo" id="banner_photo_input" class="form-control ph-input" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>
        </div>
        <div class="col-lg-8">
            <div class="ph-card p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">First Name</label>
                        <input type="text" name="first_name" class="form-control ph-input" value="{{ old('first_name', auth()->user()->first_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Last Name</label>
                        <input type="text" name="last_name" class="form-control ph-input" value="{{ old('last_name', auth()->user()->last_name) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Organization Name</label>
                        <input type="text" name="organization_name" class="form-control ph-input" value="{{ old('organization_name', $profile->organization_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Phone</label>
                        <input type="text" name="phone" class="form-control ph-input" value="{{ old('phone', $profile->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Website</label>
                        <input type="url" name="website" class="form-control ph-input" value="{{ old('website', $profile->website) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Location</label>
                        @include('partials.location-select', [
                            'region' => $profile->region,
                            'city' => $profile->city,
                            'barangay' => $profile->barangay,
                        ])
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Bio</label>
                        <textarea name="bio" class="form-control ph-input" rows="4">{{ old('bio', $profile->bio) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn ph-btn-primary mt-4">Save Profile</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const wrap = document.getElementById('bannerReposition');
    const input = document.getElementById('bannerPositionYInput');
    const hint = wrap.querySelector('.banner-reposition-hint');
    const placeholder = wrap.querySelector('.banner-reposition-placeholder');
    const resetBtn = document.getElementById('bannerResetPosition');
    const fileInput = document.getElementById('banner_photo_input');

    let dragging = false;
    let startClientY = 0;
    let startPosition = parseInt(input.value, 10) || 50;

    function hasImage() {
        return wrap.style.backgroundImage && wrap.style.backgroundImage !== 'none';
    }

    function setPosition(value) {
        value = Math.max(0, Math.min(100, Math.round(value)));
        input.value = value;
        wrap.style.backgroundPosition = 'center ' + value + '%';
    }

    wrap.addEventListener('pointerdown', (e) => {
        if (!hasImage()) return;
        dragging = true;
        startClientY = e.clientY;
        startPosition = parseInt(input.value, 10) || 50;
        wrap.setPointerCapture(e.pointerId);
        hint.classList.remove('d-none');
        e.preventDefault();
    });

    wrap.addEventListener('pointermove', (e) => {
        if (!dragging) return;
        const deltaY = e.clientY - startClientY;
        const range = wrap.offsetHeight || 140;
        setPosition(startPosition - (deltaY / range) * 100);
    });

    ['pointerup', 'pointercancel', 'pointerleave'].forEach((evt) => {
        wrap.addEventListener(evt, () => {
            dragging = false;
            hint.classList.add('d-none');
        });
    });

    resetBtn.addEventListener('click', () => setPosition(50));

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];

        if (!file) return;

        wrap.style.backgroundImage = "url('" + URL.createObjectURL(file) + "')";
        wrap.classList.remove('banner-reposition--empty');
        if (placeholder) placeholder.remove();
        setPosition(50);
    });
})();
</script>
@endpush
@endsection
