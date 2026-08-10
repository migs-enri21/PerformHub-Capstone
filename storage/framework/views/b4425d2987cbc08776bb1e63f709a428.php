<?php $__env->startSection('title', 'Event Type Management'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Event Type Management</h2>
</div>

<div class="ph-card p-4 mb-4">
    <form method="GET" action="<?php echo e(route('admin.event-types.index')); ?>" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control ph-input" placeholder="Search by name or description" value="<?php echo e($search ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select ph-input">
                <option value="">All Status</option>
                <option value="active" <?php echo e((request('status') === 'active') ? 'selected' : ''); ?>>Active</option>
                <option value="inactive" <?php echo e((request('status') === 'inactive') ? 'selected' : ''); ?>>Inactive</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
            <a href="<?php echo e(route('admin.event-types.index')); ?>" class="btn btn-outline-secondary flex-grow-1">Reset</a>
        </div>
    </form>
</div>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-bold mb-3">Create Event Type</h5>
    <form method="POST" action="<?php echo e(route('admin.event-types.store')); ?>" class="row g-3 align-items-end">
        <?php echo csrf_field(); ?>
        <div class="col-md-4">
            <input type="text" name="name" class="form-control ph-input" placeholder="Event type name" required>
        </div>
        <div class="col-md-5">
            <input type="text" name="description" class="form-control ph-input" placeholder="Description">
        </div>
        <div class="col-md-3">
            <button class="btn ph-btn-primary w-100">Add Event Type</button>
        </div>
    </form>
</div>

<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $eventTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eventType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($eventType->name); ?></td>
                    <td><?php echo e($eventType->description ?? '—'); ?></td>
                    <td>
                        <span class="badge <?php echo e($eventType->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                            <?php echo e($eventType->is_active ? 'Active' : 'Inactive'); ?>

                        </span>
                    </td>
                    <td class="text-end">
                        <a href="<?php echo e(route('admin.event-types.show', $eventType)); ?>" class="btn btn-sm btn-outline-info">View</a>
                        <a href="<?php echo e(route('admin.event-types.edit', $eventType)); ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                        <form method="POST" action="<?php echo e(route('admin.event-types.toggle', $eventType)); ?>" class="d-inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-sm <?php echo e($eventType->is_active ? 'btn-outline-secondary' : 'btn-outline-success'); ?>">
                                <?php echo e($eventType->is_active ? 'Deactivate' : 'Activate'); ?>

                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.event-types.destroy', $eventType)); ?>" class="d-inline" onsubmit="return confirm('Delete this event type?');">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">No event types found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4">
    <?php echo e($eventTypes->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/admin/event-types/index.blade.php ENDPATH**/ ?>