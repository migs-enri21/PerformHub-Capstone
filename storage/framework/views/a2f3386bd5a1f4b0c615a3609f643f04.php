<?php $__env->startSection('title', 'Monitor Bookings'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Booking Records</h2>
</div>

<div class="ph-card p-4 mb-4">
    <form method="GET" action="<?php echo e(route('admin.monitoring.bookings')); ?>" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control ph-input" placeholder="Event name, venue, requirements" value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select ph-input">
                <option value="">All</option>
                <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Pending</option>
                <option value="accepted" <?php if(request('status') === 'accepted'): echo 'selected'; endif; ?>>Accepted</option>
                <option value="rejected" <?php if(request('status') === 'rejected'): echo 'selected'; endif; ?>>Rejected</option>
                <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Completed</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Organizer</label>
            <select name="organizer_id" class="form-select ph-input">
                <option value="">All Organizers</option>
                <?php $__currentLoopData = $organizers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $organizer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($organizer->id); ?>" <?php if(request('organizer_id') == $organizer->id): echo 'selected'; endif; ?>><?php echo e($organizer->fullName()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="date_from" class="form-control ph-input" value="<?php echo e(request('date_from')); ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="date_to" class="form-control ph-input" value="<?php echo e(request('date_to')); ?>">
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary">Filter</button>
            <a href="<?php echo e(route('admin.monitoring.bookings')); ?>" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead>
            <tr>
                <th>Event</th>
                <th>Organizer</th>
                <th>Performer</th>
                <th>Date</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <strong><?php echo e($b->event_name); ?></strong>
                        <?php if($b->venue): ?><div class="small text-muted"><?php echo e($b->venue); ?></div><?php endif; ?>
                    </td>
                    <td><?php echo e($b->organizer?->fullName() ?? '—'); ?></td>
                    <td><?php echo e($b->performer?->fullName() ?? '—'); ?></td>
                    <td>
                        <?php echo e(optional($b->event_date)->format('M d, Y')); ?>

                        <?php if($b->event_time): ?><div class="small text-muted"><?php echo e(\Carbon\Carbon::parse($b->event_time)->format('g:i A')); ?></div><?php endif; ?>
                    </td>
                    <td><span class="badge <?php echo e($b->statusBadgeClass()); ?>"><?php echo e($b->statusLabel()); ?></span></td>
                    <td><?php echo e($b->created_at->format('M d, Y h:i A')); ?></td>
                    <td>
                        <a href="<?php echo e(route('admin.events.show', $b)); ?>" class="btn btn-sm btn-outline-info">View</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">No bookings found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4">
    <?php echo e($bookings->appends(request()->query())->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\GitHub\PerformHub-Capstone\resources\views/admin/monitoring/bookings.blade.php ENDPATH**/ ?>