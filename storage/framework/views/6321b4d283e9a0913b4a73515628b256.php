<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<h2 class="fw-bold mb-4">Admin Dashboard</h2>
<div class="row g-4 mb-4">
    <?php $__currentLoopData = [['label'=>'Total Users','value'=>$stats['users']],['label'=>'Performers','value'=>$stats['performers']],['label'=>'Organizers','value'=>$stats['organizers']],['label'=>'Bookings','value'=>$stats['bookings']],['label'=>'Pending Verifications','value'=>$stats['pending_verifications']]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-4 col-lg-2">
            <div class="ph-card p-3 stat-card text-center">
                <h4 class="fw-bold mb-0"><?php echo e($stat['value']); ?></h4>
                <small class="text-muted"><?php echo e($stat['label']); ?></small>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<div class="ph-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-semibold mb-0">Recent Bookings</h5>
        <a href="<?php echo e(route('admin.monitoring.bookings')); ?>" class="btn btn-sm btn-outline-primary">View All Bookings</a>
    </div>

    <div class="list-group list-group-flush">
        <?php $__empty_1 = true; $__currentLoopData = $recentBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 border-bottom">
                <div>
                    <div class="fw-semibold"><?php echo e($b->event_name); ?></div>
                    <small class="text-muted"><?php echo e($b->organizer?->fullName() ?? '—'); ?> · <?php echo e($b->performer?->fullName() ?? '—'); ?></small>
                </div>
                <span class="badge <?php echo e($b->statusBadgeClass()); ?>"><?php echo e($b->statusLabel()); ?></span>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-muted py-3">No recent bookings yet.</div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\GitHub\PerformHub-Capstone\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>