<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'items',
    'performer',
    'editable' => false,
    'isOwn' => false,
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
    'items',
    'performer',
    'editable' => false,
    'isOwn' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $first = $items->first();
    $postedAt = $first->created_at;
    $photoUrl = $performer->profilePhotoUrl()
        ?? 'https://ui-avatars.com/api/?name='.urlencode($performer->stage_name).'&background=6346ff&color=fff&size=128';
    $profileUrl = $isOwn ? route('performer.profile.show') : route('talent.show', $performer);
?>

<article class="portfolio-feed-post">
    <div class="portfolio-feed-post-header px-3 py-3 d-flex align-items-center gap-3">
        <a href="<?php echo e($profileUrl); ?>" class="flex-shrink-0">
            <img src="<?php echo e($photoUrl); ?>" alt="" class="rounded-circle portfolio-feed-avatar" width="44" height="44">
        </a>
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="<?php echo e($profileUrl); ?>" class="text-white text-decoration-none fw-semibold mb-0">
                    <?php echo e($performer->stage_name); ?>

                </a>
                <?php if($isOwn): ?>
                    <span class="badge rounded-pill" style="background: rgba(99, 70, 255, 0.2); color: #c4b5fd;">Your post</span>
                <?php endif; ?>
            </div>
            <small class="text-muted d-block">
                <?php echo e($performer->categoryNames() ?: 'Performer'); ?>

            </small>
            <small class="text-muted"><?php echo e($postedAt->diffForHumans()); ?></small>
        </div>
    </div>

    <?php echo $__env->make('partials.portfolio-collage', ['items' => $items, 'editable' => $editable], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if (! ($editable || $isOwn)): ?>
        <div class="portfolio-feed-post-actions p-3 pt-0 d-flex flex-wrap gap-2">
            <?php if(auth()->user()->isOrganizer() && ! $isOwn): ?>
                <?php if(auth()->user()->hasLimitedAccess()): ?>
                    <a href="<?php echo e(auth()->user()->onboardingRoute()); ?>" class="btn btn-sm ph-btn-primary">
                        <i class="fas fa-lock me-1"></i> Complete sign-up to book
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('organizer.bookings.create', $performer)); ?>" class="btn btn-sm ph-btn-primary">
                        Send Booking Request
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</article>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/partials/portfolio-feed-post.blade.php ENDPATH**/ ?>