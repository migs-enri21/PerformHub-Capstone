<?php $__env->startSection('title', 'Edit Event Type'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-4">
    <a href="<?php echo e(route('admin.categories.index')); ?>" class="btn btn-outline-secondary mb-3">← Back to Categories</a>
    <h2 class="fw-bold">Edit Event Type: <?php echo e($eventType->name); ?></h2>
</div>

<div class="ph-card p-4">
    <form method="POST" action="<?php echo e(route('admin.event-types.update', $eventType)); ?>" class="row g-3">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="col-md-6">
            <label class="form-label">Event Type Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control ph-input <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('name', $eventType->name)); ?>" required>
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
                <input type="checkbox" name="is_active" class="form-check-input" value="1" <?php echo e(old('is_active', $eventType->is_active) ? 'checked' : ''); ?> id="is_active">
                <label class="form-check-label" for="is_active">This event type is active</label>
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
unset($__errorArgs, $__bag); ?>"><?php echo e(old('description', $eventType->description)); ?></textarea>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\admin\event-types\edit.blade.php ENDPATH**/ ?>