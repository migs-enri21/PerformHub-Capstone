<?php $__env->startSection('title', 'All Users'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<h2 class="fw-bold mb-4">All Users</h2>
<div class="ph-card p-4">
    <table class="table table-dark table-hover table-sm mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e(Str::title($user->name)); ?></td>
                    <td><?php echo e(ucfirst($user->role)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\admin\users\all.blade.php ENDPATH**/ ?>