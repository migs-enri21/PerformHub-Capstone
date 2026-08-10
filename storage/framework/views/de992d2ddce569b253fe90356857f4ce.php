<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['performer', 'editable' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['performer', 'editable' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $stats = $performer->socialStats();
    $missingCounts = collect($stats)->contains(fn ($stat) => $stat['count'] === null);
?>

<?php if(count($stats)): ?>
    <div class="ph-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">Social Media</h5>

        <?php if($editable && $missingCounts): ?>
            <p class="text-muted small mb-3">
                Add follower counts in <a href="<?php echo e(route('performer.profile.edit')); ?>">Edit Profile</a> (e.g. enter <code>19000</code> to show as 19K).
            </p>
        <?php endif; ?>

        <div class="row g-3">
            <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $platform => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-6 col-lg-3">
                    <a href="<?php echo e($stat['url']); ?>" target="_blank" rel="noopener" class="social-stat-card social-stat-card--<?php echo e($platform); ?>">
                        <span class="social-stat-icon">
                            <i class="fab fa-<?php echo e($platform); ?>"></i>
                        </span>
                        <span class="social-stat-body">
                            <?php if($stat['count'] !== null): ?>
                                <span class="social-stat-count"><?php echo e($stat['formatted']); ?></span>
                                <span class="social-stat-metric"><?php echo e($stat['metric']); ?></span>
                            <?php else: ?>
                                <span class="social-stat-count social-stat-count--empty">—</span>
                                <span class="social-stat-metric">Add <?php echo e(strtolower($stat['metric'])); ?></span>
                            <?php endif; ?>
                            <span class="social-stat-platform"><?php echo e($stat['label']); ?></span>
                        </span>
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/partials/social-media-section.blade.php ENDPATH**/ ?>