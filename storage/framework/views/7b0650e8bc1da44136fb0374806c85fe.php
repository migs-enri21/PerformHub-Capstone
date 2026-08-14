<link href="<?php echo e(asset('css/performhub-base.css')); ?>?v=<?php echo e(filemtime(public_path('css/performhub-base.css'))); ?>" rel="stylesheet">
<?php if(auth()->guard()->check()): ?>
    <?php if(auth()->user()->isPerformer()): ?>
        <link href="<?php echo e(asset('css/performer.css')); ?>?v=<?php echo e(filemtime(public_path('css/performer.css'))); ?>" rel="stylesheet">
    <?php elseif(auth()->user()->isOrganizer()): ?>
        <link href="<?php echo e(asset('css/organizer.css')); ?>?v=<?php echo e(filemtime(public_path('css/organizer.css'))); ?>" rel="stylesheet">
        <link href="<?php echo e(asset('css/performer.css')); ?>?v=<?php echo e(filemtime(public_path('css/performer.css'))); ?>" rel="stylesheet">
    <?php elseif(auth()->user()->isAdmin()): ?>
        <link href="<?php echo e(asset('css/admin.css')); ?>?v=<?php echo e(filemtime(public_path('css/admin.css'))); ?>" rel="stylesheet">
    <?php endif; ?>
<?php else: ?>
    <link href="<?php echo e(asset('css/organizer.css')); ?>?v=<?php echo e(filemtime(public_path('css/organizer.css'))); ?>" rel="stylesheet">
<?php endif; ?>
<link href="<?php echo e(asset('css/performhub-light.css')); ?>?v=<?php echo e(filemtime(public_path('css/performhub-light.css'))); ?>" rel="stylesheet">
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\partials\stylesheets.blade.php ENDPATH**/ ?>