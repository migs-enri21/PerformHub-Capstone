@props([
    'name' => 'profile_photo',
    'currentUrl' => '',
    'fallbackName' => 'User',
    'formId' => 'profileForm',
])

@php
    $fallbackUrl = 'https://ui-avatars.com/api/?name='.urlencode($fallbackName).'&background=6346ff&color=fff&size=256';
    $photoUrl = $currentUrl ?: $fallbackUrl;
@endphp

<div class="profile-photo-upload">
    <label class="profile-photo-picker" for="{{ $name }}_input" aria-label="Upload profile photo">
        <img
            src="{{ $photoUrl }}"
            alt=""
            class="profile-photo-preview rounded-circle"
            id="{{ $name }}_preview"
            width="128"
            height="128"
            data-fallback="{{ $fallbackUrl }}"
            onerror="this.onerror=null;this.src=this.dataset.fallback;"
        >
        <span class="profile-photo-overlay rounded-circle">
            <i class="fas fa-camera"></i>
            <span class="small d-block mt-1">Choose photo</span>
        </span>
    </label>
    <input
        type="file"
        name="{{ $name }}"
        id="{{ $name }}_input"
        class="d-none"
        accept="image/jpeg,image/png,image/webp,image/gif"
    >
    <p class="text-muted small mb-2 profile-photo-hint" id="{{ $name }}_hint">
        Click the photo to choose an image<br>JPG, PNG, WEBP · max 5 MB
    </p>
    <button
        type="submit"
        form="{{ $formId }}"
        class="btn ph-btn-primary btn-sm profile-photo-apply d-none"
        id="{{ $name }}_apply"
    >
        Apply Photo
    </button>
</div>

@once
    @push('scripts')
        <script>
        document.querySelectorAll('.profile-photo-upload').forEach(wrapper => {
            const input = wrapper.querySelector('input[type=file]');
            const preview = wrapper.querySelector('.profile-photo-preview');
            const hint = wrapper.querySelector('.profile-photo-hint');
            const applyBtn = wrapper.querySelector('.profile-photo-apply');

            input.addEventListener('change', () => {
                const file = input.files[0];

                if (!file) {
                    applyBtn.classList.add('d-none');
                    hint.innerHTML = 'Click the photo to choose an image<br>JPG, PNG, WEBP · max 5 MB';
                    hint.classList.remove('text-success');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('Photo must be 5 MB or smaller.');
                    input.value = '';
                    applyBtn.classList.add('d-none');
                    return;
                }

                preview.src = URL.createObjectURL(file);
                hint.innerHTML = '<span class="text-success">' + file.name + '</span><br>Ready to upload';
                hint.classList.add('text-success');
                applyBtn.classList.remove('d-none');
            });
        });
        </script>
    @endpush
@endonce
