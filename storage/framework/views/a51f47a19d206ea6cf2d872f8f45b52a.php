<?php $__env->startSection('title', 'Edit Category'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <a href="<?php echo e(route('admin.categories.index')); ?>" class="btn btn-outline-secondary mb-3">← Back to Categories</a>
    <h2 class="fw-bold">Edit Category: <?php echo e($category->name); ?></h2>
</div>

<div class="ph-card p-4">
    <form method="POST" action="<?php echo e(route('admin.categories.update', $category)); ?>" class="row g-3">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        
        <div class="col-md-6">
            <label class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control ph-input <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('name', $category->name)); ?>" required>
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">Active Status</label>
            <div class="form-check mt-2">
                <input type="checkbox" name="is_active" class="form-check-input" value="1" <?php echo e(old('is_active', $category->is_active) ? 'checked' : ''); ?> id="is_active">
                <label class="form-check-label" for="is_active">
                    This category is active
                </label>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="4" class="form-control ph-input <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('description', $category->description)); ?></textarea>
            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="col-12">
            <button type="submit" class="btn ph-btn-primary">Save Changes</button>
            <a href="<?php echo e(route('admin.categories.index')); ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php if($category->performers->count() > 0): ?>
<div class="ph-card p-4 mt-4">
    <h5 class="fw-bold mb-3">Performers in this category (<?php echo e($category->performers->count()); ?>)</h5>
    <div class="table-responsive">
        <table class="table table-sm table-dark">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $category->performers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $performer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($performer->user->name ?? 'N/A'); ?></td>
                        <td><?php echo e($performer->user->email ?? 'N/A'); ?></td>
                        <td><span class="badge bg-info">Performer</span></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\admin\categories\edit.blade.php ENDPATH**/ ?>