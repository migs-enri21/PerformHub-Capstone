<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'profile_photo',
    'currentUrl' => '',
    'fallbackName' => 'User',
    'formId' => 'profileForm',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'name' => 'profile_photo',
    'currentUrl' => '',
    'fallbackName' => 'User',
    'formId' => 'profileForm',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $fallbackUrl = 'https://ui-avatars.com/api/?name='.urlencode($fallbackName).'&background=6346ff&color=fff&size=256';
    $photoUrl = $currentUrl ?: $fallbackUrl;
?>

<div class="profile-photo-upload">
    <label class="profile-photo-picker" for="<?php echo e($name); ?>_input" aria-label="Upload profile photo">
        <img
            src="<?php echo e($photoUrl); ?>"
            alt=""
            class="profile-photo-preview rounded-circle"
            id="<?php echo e($name); ?>_preview"
            width="128"
            height="128"
            data-fallback="<?php echo e($fallbackUrl); ?>"
            onerror="this.onerror=null;this.src=this.dataset.fallback;"
        >
        <span class="profile-photo-overlay rounded-circle">
            <i class="fas fa-camera"></i>
            <span class="small d-block mt-1">Choose photo</span>
        </span>
    </label>
    <input
        type="file"
        name="<?php echo e($name); ?>"
        id="<?php echo e($name); ?>_input"
        class="d-none"
        accept="image/jpeg,image/png,image/webp,image/gif"
    >
    <p class="text-muted small mb-2 profile-photo-hint" id="<?php echo e($name); ?>_hint">
        JPG, PNG, WEBP · max 5 MB
    </p>
    <label for="<?php echo e($name); ?>_input" class="btn ph-btn-outline btn-sm profile-photo-choose">
        <i class="fas fa-upload me-1"></i> Choose Photo
    </label>
    <button
        type="submit"
        form="<?php echo e($formId); ?>"
        class="btn ph-btn-primary btn-sm profile-photo-apply d-none"
        id="<?php echo e($name); ?>_apply"
    >
        Apply Photo
    </button>
</div>

<?php if (! $__env->hasRenderedOnce('6b14cce6-828f-489e-a62e-ef70f09d18bd')): $__env->markAsRenderedOnce('6b14cce6-828f-489e-a62e-ef70f09d18bd'); ?>
    <?php $__env->startPush('scripts'); ?>
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
                    hint.innerHTML = 'JPG, PNG, WEBP · max 5 MB';
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
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\partials\profile-photo-upload.blade.php ENDPATH**/ ?>