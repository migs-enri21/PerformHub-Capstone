<?php $__env->startSection('title', 'Categories'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Category Management</h2>
</div>

<!-- Filter Section -->
<div class="ph-card p-4 mb-4">
    <form method="GET" action="<?php echo e(route('admin.categories.index')); ?>" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control ph-input" placeholder="Search by name or description" value="<?php echo e($search ?? ''); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control ph-input">
                <option value="">All Status</option>
                <option value="active" <?php echo e((request('status') === 'active') ? 'selected' : ''); ?>>Active</option>
                <option value="inactive" <?php echo e((request('status') === 'inactive') ? 'selected' : ''); ?>>Inactive</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn ph-btn-primary flex-grow-1">Filter</button>
            <a href="<?php echo e(route('admin.categories.index')); ?>" class="btn btn-outline-secondary flex-grow-1">Reset</a>
        </div>
    </form>
</div>

<!-- Add New Category Form -->
<div class="ph-card p-4 mb-4">
    <form method="POST" action="<?php echo e(route('admin.categories.store')); ?>" class="row g-3 align-items-end">
        <?php echo csrf_field(); ?>
        <div class="col-md-3"><input type="text" name="name" class="form-control ph-input" placeholder="Category name" required></div>
        <div class="col-md-3"><input type="text" name="icon" class="form-control ph-input" placeholder="fa-music"></div>
        <div class="col-md-4"><input type="text" name="description" class="form-control ph-input" placeholder="Description"></div>
        <div class="col-md-2"><button class="btn ph-btn-primary w-100">Add Category</button></div>
    </form>
</div>

<!-- Categories Table -->
<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead>
            <tr>
                <th class="w-20">Name</th>
                <th class="w-45">Description</th>
                <th class="w-15">Status</th>
                <th class="w-12 text-center">Performers</th>
                <th class="w-18 text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="align-middle">
                        <strong><?php echo e($cat->name); ?></strong>
                    </td>
                    <td class="align-middle">
                        <p class="mb-0 text-truncate" style="max-width: 350px;"><?php echo e($cat->description ?? '—'); ?></p>
                    </td>
                    <td class="align-middle">
                        <span class="badge <?php echo e($cat->is_active ? 'bg-success' : 'bg-secondary'); ?>">
                            <?php echo e($cat->is_active ? 'Active' : 'Inactive'); ?>

                        </span>
                    </td>
                    <td class="align-middle text-center">
                        <span class="badge bg-info"><?php echo e($cat->performers->count()); ?></span>
                    </td>
                    <td class="align-middle text-end">
                        <a href="<?php echo e(route('admin.categories.show', $cat)); ?>" class="btn btn-sm btn-outline-info" title="View">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="<?php echo e(route('admin.categories.edit', $cat)); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form method="POST" action="<?php echo e(route('admin.categories.toggle', $cat)); ?>" class="d-inline" style="display:inline-block;">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-sm <?php echo e($cat->is_active ? 'btn-outline-secondary' : 'btn-outline-success'); ?>" title="<?php echo e($cat->is_active ? 'Deactivate' : 'Activate'); ?>">
                                <i class="fas <?php echo e($cat->is_active ? 'fa-toggle-on' : 'fa-toggle-off'); ?>"></i>
                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.categories.destroy', $cat)); ?>" class="d-inline" onsubmit="return confirm('Delete this category?');" style="display:inline-block;">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                        No categories found
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4">
    <?php echo e($categories->links()); ?>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/admin/categories/index.blade.php ENDPATH**/ ?>