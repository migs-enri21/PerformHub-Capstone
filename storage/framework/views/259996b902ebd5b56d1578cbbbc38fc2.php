<?php $__env->startSection('onboarding-content'); ?>
<div class="text-center mb-4">
    <div class="onboarding-success-icon mb-3">
        <i class="fas fa-check"></i>
    </div>
    <h2 class="fw-bold mb-2">You're all set!</h2>
    <p class="text-muted">
        Your <strong><?php echo e($user->role); ?></strong> account has been created.
        <?php if($user->isOrganizer()): ?>
            Your verification documents are under review and you'll be notified within 24–48 hours.
        <?php else: ?>
            Your identity verification is under review and you'll be notified within 24–48 hours.
        <?php endif; ?>
    </p>
</div>

<div class="ph-card p-4 mb-4">
    <?php
        $checklist = [
            ['label' => 'Account Created', 'done' => true],
            ['label' => 'Profile Completed', 'done' => true],
            ['label' => 'Documents Submitted', 'done' => true],
            ['label' => 'Identity Verification', 'done' => false, 'pending' => true],
            ['label' => 'Verified Badge', 'done' => false, 'pending' => true],
        ];
    ?>
    <?php $__currentLoopData = $checklist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="d-flex justify-content-between align-items-center py-2 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>" style="border-color: var(--ph-border) !important;">
            <span class="small"><?php echo e($item['label']); ?></span>
            <?php if($item['done'] ?? false): ?>
                <span class="badge rounded-pill bg-success">Done</span>
            <?php else: ?>
                <span class="badge rounded-pill bg-warning text-dark">Pending</span>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<a href="<?php echo e($user->dashboardRoute()); ?>" class="btn ph-btn-primary w-100 mb-2">
    Go to Dashboard <i class="fas fa-arrow-right ms-2"></i>
</a>
<p class="text-center text-muted small mb-0">
    You can still use the platform while your verification is being processed.
</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('onboarding.layout', ['title' => 'All Set', 'current' => 4], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\onboarding\complete.blade.php ENDPATH**/ ?>