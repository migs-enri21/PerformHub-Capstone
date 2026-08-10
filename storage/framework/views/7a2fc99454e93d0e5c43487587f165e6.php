<?php $__env->startSection('title', 'My Profile'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-end mb-2">
    <a href="<?php echo e(route('organizer.profile.edit')); ?>" class="btn ph-btn-outline btn-sm">
        <i class="fas fa-pen me-1"></i> Edit Profile
    </a>
</div>

<?php echo $__env->make('partials.organizer-profile-header', [
    'organizer' => $profile,
    'editable' => true,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-semibold mb-3">Contact Info</h5>
            <p class="text-muted mb-1"><i class="fas fa-phone me-2"></i><?php echo e($profile->phone ?: 'No phone number set.'); ?></p>
            <p class="mb-0">
                <i class="fas fa-globe me-2 text-muted"></i>
                <?php if($profile->website): ?>
                    <a href="<?php echo e($profile->website); ?>" target="_blank" rel="noopener"><?php echo e($profile->website); ?></a>
                <?php else: ?>
                    <span class="text-muted">No website set.</span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-semibold mb-3">Location</h5>
            <p class="text-muted mb-0"><?php echo e($profile->fullLocation() ?: 'No location set yet.'); ?></p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/organizer/profile/show.blade.php ENDPATH**/ ?>