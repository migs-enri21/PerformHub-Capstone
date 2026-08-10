<?php $__env->startSection('title', 'Notifications'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('partials.role-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Notifications</h2>
    <?php if($notifications->total() > 0): ?>
        <span class="badge bg-primary"><?php echo e($notifications->total()); ?> total</span>
    <?php endif; ?>
</div>

<?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <form method="POST" action="<?php echo e(route('notifications.read', $n)); ?>" class="mb-2">
        <?php echo csrf_field(); ?>
        <div class="ph-card p-4 <?php echo e(!$n->is_read ? 'border-primary border-2' : ''); ?>" style="cursor: pointer;">
            <div class="row align-items-start">
                <div class="col">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h6 class="mb-0 fw-bold"><?php echo e($n->title); ?></h6>
                        <?php if(!$n->is_read): ?>
                            <span class="badge bg-primary">New</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted mb-2"><?php echo e($n->message); ?></p>
                    <small class="text-muted"><?php echo e($n->created_at->diffForHumans()); ?></small>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn ph-btn-primary">
                        <?php if($n->link): ?>
                            <i class="fas fa-arrow-right me-1"></i>View
                        <?php else: ?>
                            <i class="fas fa-check me-1"></i>Mark Read
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="ph-card p-5 text-center">
        <i class="fas fa-bell fa-3x text-muted mb-3"></i>
        <p class="text-muted">No notifications yet.</p>
    </div>
<?php endif; ?>

<div class="mt-4">
    <?php echo e($notifications->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/notifications/index.blade.php ENDPATH**/ ?>