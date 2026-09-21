@extends('layouts.app')

@section('title', 'Edit Profile')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Edit Profile</h2>
    <a href="{{ route('performer.profile.show') }}" class="btn ph-btn-outline btn-sm">
        <i class="fas fa-eye me-1"></i> View Profile
    </a>
</div>
<form method="POST" action="{{ route('performer.profile.update') }}" enctype="multipart/form-data" id="profileForm">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="profile-edit-aside">
            <div class="ph-card p-4 profile-photo-card" id="photo">
                @include('partials.profile-photo-upload', [
                    'currentUrl' => $profile->profilePhotoUrl(),
                    'fallbackName' => $profile->stage_name,
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
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                <label class="form-label text-muted small mb-0" for="banner_photo_input">Drag to reposition · max 5 MB</label>
                <button type="button" class="btn ph-btn-outline btn-sm flex-shrink-0" id="bannerResetPosition">Reset</button>
                </div>
                <input type="file" name="banner_photo" id="banner_photo_input" class="form-control ph-input" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="ph-card p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">First Name</label>
                        <input type="text" name="first_name" class="form-control ph-input" value="{{ old('first_name', auth()->user()->first_name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Last Name</label>
                        <input type="text" name="last_name" class="form-control ph-input" value="{{ old('last_name', auth()->user()->last_name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Stage Name</label>
                        <input type="text" name="stage_name" class="form-control ph-input" value="{{ old('stage_name', $profile->stage_name) }}" required>
                    </div>
                    @php
                        $selectedCategoryIds = collect(old('category_ids', $profile->categories->pluck('id')->all()))
                            ->map(fn ($id) => (string) $id)
                            ->all();
                        $genreGroups = \App\Support\PerformerOptionGuide::groupsForIds($optionCatalog, $selectedCategoryIds, 'genres');
                        $hourRate = old('rate_per_hour', $profile->rate_per_hour);
                        $dayRate = old('rate_per_day', $profile->rate_per_day);
                        $hourRate = $hourRate === null || $hourRate === '' ? '' : (int) round((float) $hourRate);
                        $dayRate = $dayRate === null || $dayRate === '' ? '' : (int) round((float) $dayRate);
                    @endphp
                    <div class="col-12">
                        <div class="profile-edit-divider"></div>
                        <label class="form-label text-muted small mb-1">Categories</label>
                        <p class="text-muted small mb-2">Select every role you perform.</p>
                        <div class="category-checkbox-grid" id="profileCategoryGrid">
                            @foreach($categories as $cat)
                                <label class="category-checkbox-option" data-category-name="{{ strtolower($cat->name) }}">
                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" @checked(in_array((string) $cat->id, $selectedCategoryIds, true))>
                                    <span>{{ $cat->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Specialty / Instrument</label>
                        <p class="profile-field-copy text-muted small">You can pick more than one.</p>
                        @include('partials.specialty-select', [
                            'value' => $profile->specialtyList(),
                            'multiple' => true,
                        ])
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Genre</label>
                        <p class="profile-field-copy text-muted small">Matches the roles you checked.</p>
                        @include('partials.genre-select', [
                            'value' => $profile->genreList(),
                            'multiple' => true,
                            'groups' => $genreGroups,
                            'guidedKind' => 'genres',
                            'emptyMessage' => 'No genres for the selected categories yet',
                        ])
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Rate per hour</label>
                        <div class="input-group profile-rate-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="1" min="0" name="rate_per_hour" class="form-control ph-input" value="{{ $hourRate }}" placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Rate per day</label>
                        <div class="input-group profile-rate-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="1" min="0" name="rate_per_day" class="form-control ph-input" value="{{ $dayRate }}" placeholder="0">
                        </div>
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
            </div>

            <div class="ph-card p-4 mt-3">
                <h5 class="fw-semibold mb-1">Social Media</h5>
                <p class="text-muted small mb-3">Paste the profile URL and follower count (e.g. <strong>19000</strong> = 19K).</p>
                <div class="row g-3">
                    @php
                        $socialFields = [
                            'facebook' => ['label' => 'Facebook', 'metric' => 'Followers', 'count' => 'social_facebook_followers'],
                            'instagram' => ['label' => 'Instagram', 'metric' => 'Followers', 'count' => 'social_instagram_followers'],
                            'youtube' => ['label' => 'YouTube', 'metric' => 'Subscribers', 'count' => 'social_youtube_subscribers'],
                            'tiktok' => ['label' => 'TikTok', 'metric' => 'Followers', 'count' => 'social_tiktok_followers'],
                            'twitter' => ['label' => 'Twitter/X', 'metric' => 'Followers', 'count' => 'social_twitter_followers'],
                        ];
                    @endphp
                    @foreach($socialFields as $field => $meta)
                        <div class="col-12">
                            <div class="social-edit-row p-3 rounded">
                                <label class="form-label text-muted small mb-2">{{ $meta['label'] }}</label>
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <input type="url" name="social_{{ $field }}" class="form-control ph-input" value="{{ old('social_'.$field, $profile->{'social_'.$field}) }}" placeholder="https://">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="number" min="0" name="{{ $meta['count'] }}" class="form-control ph-input" value="{{ old($meta['count'], $profile->{$meta['count']}) }}" placeholder="0">
                                            <span class="input-group-text social-metric-suffix">{{ $meta['metric'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn ph-btn-primary mt-4">Save Profile</button>
        </div>
    </div>
</form>

<div class="modal fade" id="optionRequestModal" tabindex="-1" aria-labelledby="optionRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('performer.feature-requests.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="type" id="optionRequestType" value="specialty">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="optionRequestModalLabel">Request a specialty</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Admin will review this. It shows in the list after approval.</p>
                <div id="optionRequestCategoryWrap" class="mb-3 d-none">
                    <label class="form-label" for="optionRequestCategory">Category</label>
                    <select name="category_id" id="optionRequestCategory" class="form-select ph-input">
                        <option value="">Select category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="form-label" for="optionRequestName">Name</label>
                <input type="text" name="name" id="optionRequestName" class="form-control ph-input mb-3" placeholder="e.g. Ukulele" required>
                <label class="form-label" for="optionRequestDescription">Note <span class="text-muted fw-normal">(optional)</span></label>
                <textarea name="description" id="optionRequestDescription" class="form-control ph-input" rows="3" placeholder="Why do you need this?"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn ph-btn-primary">Send Request</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const optionCatalog = @json($optionCatalog);
    const categoryInputs = document.querySelectorAll('#profileCategoryGrid input[name="category_ids[]"]');

    function selectedCategoryIds() {
        return Array.from(categoryInputs)
            .filter((input) => input.checked)
            .map((input) => String(input.value));
    }

    function groupsFor(kind) {
        return selectedCategoryIds()
            .map((id) => optionCatalog[id])
            .filter(Boolean)
            .map((entry) => ({
                label: entry.name,
                options: entry[kind] || [],
            }))
            .filter((group) => group.options.length);
    }

    function refreshGuidedSelects() {
        if (!window.PhSelect) {
            return;
        }

        document.querySelectorAll('[data-guided-kind]').forEach((wrap) => {
            window.PhSelect.setGroups(wrap, groupsFor(wrap.dataset.guidedKind));
        });
    }

    categoryInputs.forEach((input) => {
        input.addEventListener('change', refreshGuidedSelects);
    });
})();

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
