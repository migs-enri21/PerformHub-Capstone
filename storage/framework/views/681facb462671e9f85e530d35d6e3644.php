<?php $__env->startSection('onboarding-content'); ?>
<h2 class="fw-bold text-center mb-1">Your information</h2>
<p class="text-muted text-center mb-4">
    Set up your <?php echo e($user->isPerformer() ? 'performer' : 'organizer'); ?> profile
</p>

<form method="POST" action="<?php echo e(route('onboarding.profile.store')); ?>">
    <?php echo csrf_field(); ?>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label text-muted small">First Name</label>
            <input type="text" name="first_name" class="form-control ph-input" value="<?php echo e(old('first_name', $user->first_name)); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label text-muted small">Last Name</label>
            <input type="text" name="last_name" class="form-control ph-input" value="<?php echo e(old('last_name', $user->last_name)); ?>" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label text-muted small">Email Address</label>
        <input type="email" class="form-control ph-input" value="<?php echo e($user->email); ?>" disabled>
    </div>

    <div class="mb-3">
        <label class="form-label text-muted small">Phone Number</label>
        <input type="text" name="phone" class="form-control ph-input" value="<?php echo e(old('phone', $user->phone)); ?>" placeholder="+63 9XX XXX XXXX" required>
    </div>

    <div class="mb-4">
        <label class="form-label text-muted small mb-2">Location</label>
        <?php
            $profile = $user->isPerformer() ? $user->performerProfile : $user->organizerProfile;
        ?>
        <?php echo $__env->make('partials.location-select', [
            'region' => $profile?->region,
            'city' => $profile?->city,
            'barangay' => $profile?->barangay,
            'required' => true,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="d-flex gap-2">
        <a href="<?php echo e(route('onboarding.role')); ?>" class="btn ph-btn-outline">Back</a>
        <button type="submit" class="btn ph-btn-primary flex-grow-1">
            Continue <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('onboarding.layout', ['title' => 'Your Information', 'current' => 2], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/onboarding/profile.blade.php ENDPATH**/ ?>