<?php $__env->startSection('title', 'Organizer Profile'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Edit Profile</h2>
    <a href="<?php echo e(route('organizer.profile.show')); ?>" class="btn ph-btn-outline btn-sm">
        <i class="fas fa-eye me-1"></i> View Profile
    </a>
</div>
<form method="POST" action="<?php echo e(route('organizer.profile.update')); ?>" enctype="multipart/form-data" id="profileForm">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="ph-card p-4 profile-photo-card" id="photo">
                <?php echo $__env->make('partials.profile-photo-upload', [
                    'currentUrl' => $profile->profilePhotoUrl(),
                    'fallbackName' => $profile->organization_name,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div class="ph-card p-4 mt-4" id="banner">
                <h5 class="fw-semibold mb-3">Banner Image</h5>
                <?php
                    $bannerPositionY = old('banner_position_y', $profile->banner_position_y);
                ?>
                <div
                    class="banner-reposition rounded mb-2 <?php echo e($profile->bannerPhotoUrl() ? '' : 'banner-reposition--empty'); ?>"
                    id="bannerReposition"
                    style="<?php echo e($profile->bannerPhotoUrl() ? "background-image: url('".$profile->bannerPhotoUrl()."');" : ''); ?> background-position: center <?php echo e($bannerPositionY); ?>%;"
                >
                    <?php if (! ($profile->bannerPhotoUrl())): ?>
                        <span class="banner-reposition-placeholder">No banner uploaded yet</span>
                    <?php endif; ?>
                    <span class="banner-reposition-hint d-none"><i class="fas fa-arrows-up-down me-1"></i>Drag to reposition</span>
                </div>
                <input type="hidden" name="banner_position_y" id="bannerPositionYInput" value="<?php echo e($bannerPositionY); ?>">
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
                        <input type="text" name="first_name" class="form-control ph-input" value="<?php echo e(old('first_name', auth()->user()->first_name)); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Last Name</label>
                        <input type="text" name="last_name" class="form-control ph-input" value="<?php echo e(old('last_name', auth()->user()->last_name)); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Organization Name</label>
                        <input type="text" name="organization_name" class="form-control ph-input" value="<?php echo e(old('organization_name', $profile->organization_name)); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Phone</label>
                        <input type="text" name="phone" class="form-control ph-input" value="<?php echo e(old('phone', $profile->phone)); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Website</label>
                        <input type="url" name="website" class="form-control ph-input" value="<?php echo e(old('website', $profile->website)); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Location</label>
                        <?php echo $__env->make('partials.location-select', [
                            'region' => $profile->region,
                            'city' => $profile->city,
                            'barangay' => $profile->barangay,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Bio</label>
                        <textarea name="bio" class="form-control ph-input" rows="4"><?php echo e(old('bio', $profile->bio)); ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn ph-btn-primary mt-4">Save Profile</button>
            </div>
        </div>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\organizer\profile\edit.blade.php ENDPATH**/ ?>