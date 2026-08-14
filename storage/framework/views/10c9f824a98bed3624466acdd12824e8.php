<?php $__env->startSection('title', 'View Category'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <a href="<?php echo e(route('admin.categories.index')); ?>" class="btn btn-outline-secondary mb-3">← Back to Categories</a>
    <h2 class="fw-bold"><?php echo e($category->name); ?></h2>
</div>

<div class="row">
    <!-- Category Details -->
    <div class="col-md-6">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Category Details</h5>
            
            <div class="mb-3">
                <label class="form-label text-muted">Name</label>
                <p class="fw-bold"><?php echo e($category->name); ?></p>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Status</label>
                <p>
                    <span class="badge <?php echo e($category->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                        <?php echo e($category->is_active ? 'Active' : 'Inactive'); ?>

                    </span>
                </p>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Description</label>
                <p><?php echo e($category->description ?? 'No description'); ?></p>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Created</label>
                <p><?php echo e($category->created_at->format('M d, Y H:i')); ?></p>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted">Last Updated</label>
                <p><?php echo e($category->updated_at->format('M d, Y H:i')); ?></p>
            </div>

            <div class="mt-4 pt-3 border-top">
                <a href="<?php echo e(route('admin.categories.edit', $category)); ?>" class="btn ph-btn-primary">Edit</a>
                <form method="POST" action="<?php echo e(route('admin.categories.destroy', $category)); ?>" class="d-inline" onsubmit="return confirm('Are you sure?');">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Performers in this Category -->
    <div class="col-md-6">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Performers (<?php echo e($category->performers->count()); ?>)</h5>
            
            <?php if($category->performers->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-dark table-hover">
                        <thead>
                            <tr><th>Performer</th><th>Email</th></tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $category->performers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $performer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($performer->user->name ?? 'N/A'); ?></strong>
                                    </td>
                                    <td><?php echo e($performer->user->email ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-4">No performers in this category yet</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\admin\categories\show.blade.php ENDPATH**/ ?>