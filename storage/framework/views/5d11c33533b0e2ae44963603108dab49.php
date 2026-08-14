<?php $__env->startSection('title', $title ?? 'Complete Sign Up'); ?>

<?php $__env->startSection('content'); ?>
<div class="onboarding-page py-4 py-lg-5">
    <div class="container" style="max-width: 640px;">
        <a href="<?php echo e(route('home')); ?>" class="text-muted small mb-4 d-inline-block">
            <i class="fas fa-chevron-left me-1"></i> Back to Home
        </a>

        <div class="text-center mb-4">
            <a href="<?php echo e(route('home')); ?>" class="text-white text-decoration-none fw-bold fs-5 d-inline-flex align-items-center">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="PerformHub" height="36" width="36" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub
            </a>
        </div>

        <?php echo $__env->make('onboarding.partials.stepper', ['current' => $current ?? 1], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->yieldContent('onboarding-content'); ?>

        <p class="text-center text-muted small mt-4 mb-0">
            Already have an account? <a href="<?php echo e(route('login')); ?>">Sign in</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\onboarding\layout.blade.php ENDPATH**/ ?>