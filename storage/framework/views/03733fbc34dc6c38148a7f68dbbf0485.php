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
    <h5 class="fw-semibold mb-3">Recent Bookings</h5>
    <table class="table table-dark table-sm mb-0">
        <thead><tr><th>Event</th><th>Organizer</th><th>Performer</th><th>Status</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $recentBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($b->event_name); ?></td>
                    <td><?php echo e($b->organizer->name); ?></td>
                    <td><?php echo e($b->performer->name); ?></td>
                    <td><span class="badge <?php echo e($b->statusBadgeClass()); ?>"><?php echo e($b->statusLabel()); ?></span></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>