<?php $__env->startSection('title', 'User History Management'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">User History Management</h2>
</div>

<div class="ph-card p-4 mb-4">
    <form method="GET" action="<?php echo e(route('admin.events.index')); ?>" class="row g-3 align-items-end">
        <div class="col-md-8">
            <label class="form-label">Search User</label>
            <input type="text" name="user_search" class="form-control ph-input" placeholder="Erico Blaza" value="<?php echo e(request('user_search')); ?>">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary">Search</button>
            <a href="<?php echo e(route('admin.events.index')); ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Activity</th>
                <th>Event</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $userName = $event->performer?->fullName() ?? $event->organizer?->fullName() ?? '—';
                    $activity = $event->status === 'accepted' ? 'Booking Accepted' : ($event->status === 'rejected' ? 'Booking Rejected' : 'Booking Updated');
                ?>
                <tr>
                    <td><?php echo e($event->created_at->format('M d, Y')); ?></td>
                    <td><?php echo e($userName); ?></td>
                    <td><?php echo e($activity); ?></td>
                    <td><?php echo e($event->event_name); ?></td>
                    <td><span class="badge <?php echo e($event->statusBadgeClass()); ?>"><?php echo e($event->statusLabel()); ?></span></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">No user activity found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4">
    <?php echo e($events->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\GitHub\PerformHub-Capstone\resources\views/admin/events/index.blade.php ENDPATH**/ ?>