<?php $__env->startSection('title', $performer->stage_name); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('partials.role-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php echo $__env->make('partials.performer-profile-header', [
            'performer' => $performer,
            'editable' => false,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="ph-card p-4 mb-4">
            <h5 class="fw-semibold">About</h5>
            <p class="text-muted mb-0"><?php echo e($performer->bio ?? 'No bio provided.'); ?></p>
        </div>

        <?php if($portfolioGroups->isNotEmpty()): ?>
            <h5 class="fw-semibold mb-3">Portfolio Posts</h5>
            <?php echo $__env->make('partials.portfolio-feed', ['posts' => $portfolioGroups->values()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <?php if(auth()->user()->isOrganizer() && auth()->id() !== $performer->user_id): ?>
                <?php if(auth()->user()->hasLimitedAccess()): ?>
                    <a href="<?php echo e(auth()->user()->onboardingRoute()); ?>" class="btn ph-btn-primary">
                        <i class="fas fa-lock me-1"></i> Complete sign-up to book
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('organizer.bookings.create', $performer)); ?>" class="btn ph-btn-primary">Send Booking Request</a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="<?php echo e(url()->previous()); ?>" class="btn ph-btn-outline">Back</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\talent\show.blade.php ENDPATH**/ ?>