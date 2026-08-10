

<?php $__env->startSection('title', 'Click Me'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('performer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <h2 class="fw-bold mb-4">Click Me Dashboard</h2>

    <div class="ph-card p-4">
        <p class="mb-0">This is my new dashboard page.</p>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/performer/click-me.blade.php ENDPATH**/ ?>