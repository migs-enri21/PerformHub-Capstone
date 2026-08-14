<?php $__env->startSection('title', 'Manage Users'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<h2 class="fw-bold mb-4">User Management</h2>
<div class="ph-card p-4 mb-4">
    <form method="GET" class="row g-2">
        <div class="col-md-3"><select name="role" class="form-select ph-input"><option value="">All Roles</option><option value="performer" <?php if(request('role')=='performer'): echo 'selected'; endif; ?>>Performer</option><option value="organizer" <?php if(request('role')=='organizer'): echo 'selected'; endif; ?>>Organizer</option></select></div>
        <div class="col-md-3"><select name="status" class="form-select ph-input"><option value="">All Status</option><option value="active" <?php if(request('status')=='active'): echo 'selected'; endif; ?>>Active</option><option value="inactive" <?php if(request('status')=='inactive'): echo 'selected'; endif; ?>>Inactive</option></select></div>
        <div class="col-md-2"><button class="btn ph-btn-primary">Filter</button></div>
    </form>
</div>
<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead><tr><th>Name</th><th>Role</th><th class="text-center">Verified</th><th class="text-center">Status</th><th class="text-center">Actions</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e(Str::title($user->first_name)); ?> <?php echo e(Str::title($user->last_name)); ?></td>
                    <td><span class="badge bg-secondary"><?php echo e(ucfirst($user->role)); ?></span></td>
                    <td class="text-center"><?php if($user->is_verified): ?><span class="badge bg-primary">Yes</span><?php else: ?><span class="badge bg-warning text-dark">No</span><?php endif; ?></td>
                    <td class="text-center"><?php if($user->is_active): ?><span class="badge bg-success">Active</span><?php else: ?><span class="badge bg-danger">Suspended</span><?php endif; ?></td>
                    <td class="text-center">
                        <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="btn btn-sm btn-outline-info">View</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php echo e($users->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\admin\users\index.blade.php ENDPATH**/ ?>