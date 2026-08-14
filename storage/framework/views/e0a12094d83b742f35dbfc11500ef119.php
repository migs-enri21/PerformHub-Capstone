<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['photos', 'title' => '']));

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

foreach (array_filter((['photos', 'title' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $count = $photos->count();
    $layout = match (true) {
        $count === 1 => 'portfolio-collage--1',
        $count === 2 => 'portfolio-collage--2',
        $count === 3 => 'portfolio-collage--3',
        $count === 4 => 'portfolio-collage--4',
        default => 'portfolio-collage--many',
    };
    $visible = $count > 4 ? $photos->take(4) : $photos;
    $hasMore = $count > 4;
    $modalId = 'event-gallery-'.$photos->first()->id;
?>

<div
    class="portfolio-preview-collage portfolio-feed-collage event-feed-collage <?php echo e($layout); ?>"
    <?php if($hasMore): ?>
        role="button"
        tabindex="0"
        data-bs-toggle="modal"
        data-bs-target="#<?php echo e($modalId); ?>"
        aria-label="View all <?php echo e($count); ?> photos"
    <?php endif; ?>
>
    <?php $__currentLoopData = $visible; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="portfolio-collage-tile event-feed-collage-tile">
            <img src="<?php echo e($photo->fileUrl()); ?>" alt="<?php echo e($title); ?>" loading="lazy">
            <?php if($hasMore && $index === 3): ?>
                <div class="portfolio-collage-more">+<?php echo e($count - 4); ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if($hasMore): ?>
    <div class="modal fade" id="<?php echo e($modalId); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All <?php echo e($count); ?> photos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="portfolio-gallery-grid">
                        <?php $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="portfolio-gallery-item">
                                <img src="<?php echo e($photo->fileUrl()); ?>" alt="<?php echo e($title); ?>">
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\partials\event-photo-collage.blade.php ENDPATH**/ ?>