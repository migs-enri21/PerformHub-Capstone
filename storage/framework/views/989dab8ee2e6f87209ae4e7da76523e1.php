<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['event', 'applicationStatus' => null, 'bookingUrl' => null]));

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

foreach (array_filter((['event', 'applicationStatus' => null, 'bookingUrl' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $organizer = $event->organizer;
    $orgProfile = $organizer?->organizerProfile;
    $orgName = $orgProfile?->organization_name ?: ($organizer?->name ?? 'Unknown Organizer');
    $photoUrl = $orgProfile?->profilePhotoUrl()
        ?? 'https://ui-avatars.com/api/?name='.urlencode($orgName).'&background=6346ff&color=fff&size=128';
    $eventDate = $event->event_date ? \Illuminate\Support\Carbon::parse($event->event_date) : null;
    $galleryPhotos = $event->photos;
    $photoCount = $galleryPhotos->count();
?>

<article class="event-feed-post">
    <div class="event-feed-post-header">
        <img src="<?php echo e($photoUrl); ?>" alt="" class="rounded-circle event-feed-avatar flex-shrink-0" width="40" height="40">
        <div class="flex-grow-1 min-w-0">
            <p class="text-white fw-semibold mb-0 text-truncate"><?php echo e($orgName); ?></p>
            <small class="text-muted"><?php echo e($event->created_at?->diffForHumans() ?? 'Recently'); ?> · <?php echo e($event->status); ?></small>
        </div>
    </div>

    <div class="event-feed-body">
        <h5 class="text-white fw-bold mb-2 event-feed-title"><?php echo e($event->title); ?></h5>

        <?php if($event->description): ?>
            <p class="text-muted mb-2 event-feed-description"><?php echo e($event->description); ?></p>
        <?php endif; ?>

        <p class="event-feed-meta mb-0">
            <?php if($eventDate): ?>
                <span><?php echo e($eventDate->format('M j, Y')); ?><?php if($event->start_time): ?> · <?php echo e(\Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A')); ?><?php endif; ?></span>
            <?php endif; ?>
            <?php if($event->venue): ?>
                <span><?php echo e($event->venue); ?></span>
            <?php endif; ?>
            <?php if($event->preferredCategory): ?>
                <span><?php echo e($event->preferredCategory->name); ?></span>
            <?php endif; ?>
            <?php if($event->budget): ?>
                <span>₱<?php echo e(number_format((float) $event->budget, 0)); ?></span>
            <?php endif; ?>
        </p>
    </div>

    <?php if($photoCount > 1): ?>
        <?php echo $__env->make('partials.event-photo-collage', ['photos' => $galleryPhotos, 'title' => $event->title], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php elseif($photoCount === 1): ?>
        <div class="event-feed-cover">
            <img src="<?php echo e($galleryPhotos->first()->fileUrl()); ?>" alt="<?php echo e($event->title); ?>" loading="lazy">
        </div>
    <?php elseif($event->coverPhotoUrl()): ?>
        <div class="event-feed-cover">
            <img src="<?php echo e($event->coverPhotoUrl()); ?>" alt="<?php echo e($event->title); ?>" loading="lazy">
        </div>
    <?php else: ?>
        <div class="event-feed-cover event-feed-cover--empty">
            <div class="event-feed-cover-placeholder" aria-hidden="true">
                <i class="fas fa-image"></i>
            </div>
        </div>
    <?php endif; ?>

    <div class="event-feed-footer">
        <?php if($applicationStatus === 'accepted'): ?>
            <a href="<?php echo e(route('performer.profile.show')); ?>#availability" class="event-feed-footer-btn event-feed-footer-btn--accepted w-100">
                <i class="fas fa-check me-1"></i>Accepted — on your calendar
            </a>
        <?php elseif($applicationStatus === 'invited' && $bookingUrl): ?>
            <a href="<?php echo e($bookingUrl); ?>" class="event-feed-footer-btn event-feed-footer-btn--invited w-100">
                <i class="fas fa-envelope me-1"></i>Booking request — respond
            </a>
        <?php elseif($applicationStatus === 'invited'): ?>
            <a href="<?php echo e(route('performer.bookings.index', ['status' => 'pending'])); ?>" class="event-feed-footer-btn event-feed-footer-btn--invited w-100">
                <i class="fas fa-envelope me-1"></i>Booking request — respond
            </a>
        <?php elseif($applicationStatus === 'declined'): ?>
            <button type="button" class="event-feed-footer-btn event-feed-footer-btn--declined w-100" disabled>
                <i class="fas fa-times me-1"></i>Declined
            </button>
        <?php elseif($applicationStatus === 'pending'): ?>
            <button type="button" class="event-feed-footer-btn event-feed-footer-btn--applied w-100" disabled>
                <i class="fas fa-clock me-1"></i>Pending — awaiting organizer
            </button>
        <?php elseif(auth()->user()->hasLimitedAccess()): ?>
            <a href="<?php echo e(auth()->user()->onboardingRoute()); ?>" class="event-feed-footer-btn w-100">
                <i class="fas fa-lock me-1"></i>Sign up
            </a>
        <?php else: ?>
            <form method="POST" action="<?php echo e(route('performer.events.apply', $event)); ?>" class="m-0 w-100">
                <?php echo csrf_field(); ?>
                <button type="submit" class="event-feed-footer-btn w-100">
                    Apply
                </button>
            </form>
        <?php endif; ?>
    </div>
</article>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/partials/event-feed-post.blade.php ENDPATH**/ ?>