<?php if(auth()->user()->isPerformer()): ?>
    <?php echo $__env->make('performer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif(auth()->user()->isOrganizer()): ?>
    <?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php elseif(auth()->user()->isAdmin()): ?>
    <?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH D:\GitHub\PerformHub-Capstone\resources\views/partials/role-sidebar.blade.php ENDPATH**/ ?>