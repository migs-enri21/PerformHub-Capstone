<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['event']));

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

foreach (array_filter((['event']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $organizer = $event->organizer;
    $profile = $organizer?->organizerProfile;
    $name = $profile?->organization_name ?: ($organizer?->name ?? 'Organizer');
    $photoUrl = $profile?->profilePhotoUrl()
        ?? 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=6346ff&color=fff&size=128';
?>

<article class="event-feed-post mb-3">
    <div class="event-feed-post-header">
        <img src="<?php echo e($photoUrl); ?>" alt="" class="rounded-circle event-feed-avatar" width="44" height="44">
        <div>
            <strong class="event-card-organizer"><?php echo e($name); ?></strong>
            <small class="text-muted d-block">Created a new event · <?php echo e($event->created_at->diffForHumans()); ?></small>
        </div>
    </div>

    <div class="event-feed-body">
        <h5 class="event-card-title event-feed-title"><?php echo e($event->title); ?></h5>

        <?php if($event->description): ?>
            <p class="text-muted event-feed-description"><?php echo e($event->description); ?></p>
        <?php endif; ?>

        <p class="event-feed-meta mb-0">
            <span><?php echo e(\Illuminate\Support\Carbon::parse($event->event_date)->format('M j, Y')); ?></span>
            <span><?php echo e($event->venue); ?></span>
            <?php if($event->budget): ?>
                <span>₱<?php echo e(number_format((float) $event->budget, 0)); ?></span>
            <?php endif; ?>
        </p>
    </div>

    <?php if($event->photos->count() > 1): ?>
        <?php echo $__env->make('partials.event-photo-collage', ['photos' => $event->photos, 'title' => $event->title], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($event->photos->count() === 1): ?>
        <div class="event-feed-cover">
            <img src="<?php echo e($event->photos->first()->fileUrl()); ?>" alt="<?php echo e($event->title); ?>" loading="lazy">
        </div>
    <?php elseif($event->coverPhotoUrl()): ?>
        <div class="event-feed-cover">
            <img src="<?php echo e($event->coverPhotoUrl()); ?>" alt="<?php echo e($event->title); ?>" loading="lazy">
        </div>
    <?php endif; ?>

    <div class="portfolio-feed-post-actions p-3 pt-3">
        <a href="<?php echo e(route('organizer.events.show', $event)); ?>" class="btn btn-sm ph-btn-outline">View Event</a>
    </div>
</article>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\partials\event-activity-post.blade.php ENDPATH**/ ?>