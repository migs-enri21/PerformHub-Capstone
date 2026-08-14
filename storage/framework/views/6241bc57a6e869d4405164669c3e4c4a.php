<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'posts',
    'emptyMessage' => 'No posts yet. Upload photos or videos to share your work.',
    'ownProfileId' => null,
]));

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

foreach (array_filter(([
    'posts',
    'emptyMessage' => 'No posts yet. Upload photos or videos to share your work.',
    'ownProfileId' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="portfolio-feed-stream">
    <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php $performer = $items->first()->performerProfile; ?>
        <?php echo $__env->make('partials.portfolio-feed-post', [
            'items' => $items,
            'performer' => $performer,
            'isOwn' => $ownProfileId && $performer->id === $ownProfileId,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="ph-card p-4 text-center text-muted"><?php echo e($emptyMessage); ?></div>
    <?php endif; ?>
</div>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\partials\portfolio-feed.blade.php ENDPATH**/ ?>