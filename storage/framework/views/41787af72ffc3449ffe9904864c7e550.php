<?php
    $steps = [
        1 => 'Role',
        2 => 'Profile',
        3 => 'Verification',
        4 => 'Done',
    ];
?>
<div class="onboarding-stepper mb-4">
    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="onboarding-step <?php echo e($num < $current ? 'completed' : ($num === $current ? 'active' : '')); ?>">
            <div class="onboarding-step-circle">
                <?php if($num < $current): ?>
                    <i class="fas fa-check"></i>
                <?php else: ?>
                    <?php echo e($num); ?>

                <?php endif; ?>
            </div>
            <span class="onboarding-step-label"><?php echo e($label); ?></span>
        </div>
        <?php if(!$loop->last): ?>
            <div class="onboarding-step-line <?php echo e($num < $current ? 'completed' : ''); ?>"></div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\onboarding\partials\stepper.blade.php ENDPATH**/ ?>