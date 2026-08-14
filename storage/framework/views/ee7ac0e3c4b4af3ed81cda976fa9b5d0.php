<?php $__env->startSection('title', 'View Event Type'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <a href="<?php echo e(route('admin.categories.index')); ?>" class="btn btn-outline-secondary mb-3">← Back to Categories</a>
    <h2 class="fw-bold"><?php echo e($eventType->name); ?></h2>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Event Type Details</h5>
            <div class="mb-3">
                <label class="form-label text-muted">Name</label>
                <p class="fw-bold"><?php echo e($eventType->name); ?></p>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Status</label>
                <p><span class="badge <?php echo e($eventType->is_active ? 'bg-success' : 'bg-secondary'); ?>"><?php echo e($eventType->is_active ? 'Active' : 'Inactive'); ?></span></p>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Description</label>
                <p><?php echo e($eventType->description ?? 'No description'); ?></p>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Created</label>
                <p><?php echo e($eventType->created_at->format('M d, Y H:i')); ?></p>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Last Updated</label>
                <p><?php echo e($eventType->updated_at->format('M d, Y H:i')); ?></p>
            </div>
            <div class="mt-4 pt-3 border-top">
                <a href="<?php echo e(route('admin.event-types.edit', $eventType)); ?>" class="btn ph-btn-primary">Edit</a>
                <form method="POST" action="<?php echo e(route('admin.event-types.destroy', $eventType)); ?>" class="d-inline" onsubmit="return confirm('Are you sure?');">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Event Type Notes</h5>
            <p class="text-muted">This event type is a standalone classification for events. Performers are not directly linked to this model yet.</p>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\admin\event-types\show.blade.php ENDPATH**/ ?>