<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'performer',
    'editable' => false,
    'bookingUrl' => null,
    'onboardingRoute' => null,
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
    'performer',
    'editable' => false,
    'bookingUrl' => null,
    'onboardingRoute' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $photoUrl = $performer->profilePhotoUrl()
        ?? 'https://ui-avatars.com/api/?name='.urlencode($performer->stage_name).'&background=6346ff&color=fff&size=256';
    $bannerStyle = $performer->bannerPhotoUrl()
        ? "background-image: url('".$performer->bannerPhotoUrl()."'); background-position: center ".($performer->banner_position_y ?? 50)."%;"
        : '';
    $rating = $performer->averageRating();
    $subtitle = collect([$performer->categoryNames(), $performer->shortLocation()])->filter()->implode(' · ');
    $tags = $performer->displayTags();
?>

<div class="performer-profile-card ph-card mb-4">
    <div class="performer-profile-banner" style="<?php echo e($bannerStyle); ?>">
        <?php if($editable): ?>
            <a href="<?php echo e(route('performer.profile.edit')); ?>#banner" class="btn btn-sm performer-profile-banner-edit">
                <i class="fas fa-pen me-1"></i> Edit Banner
            </a>
        <?php endif; ?>
    </div>

    <div class="performer-profile-body">
        <div class="d-flex flex-column flex-md-row gap-3 gap-md-4">
            <div class="performer-profile-avatar-wrap flex-shrink-0">
                <img
                    src="<?php echo e($photoUrl); ?>"
                    alt=""
                    class="performer-profile-avatar rounded-circle"
                    width="200"
                    height="200"
                    onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?php echo e(urlencode($performer->stage_name)); ?>&background=6346ff&color=fff&size=256';"
                >
                <?php if($editable): ?>
                    <a href="<?php echo e(route('performer.profile.edit')); ?>#photo" class="performer-profile-avatar-edit" aria-label="Edit profile photo">
                        <i class="fas fa-camera"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="flex-grow-1 min-w-0">
            <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 mb-2">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h2 class="fw-bold mb-0 performer-profile-name"><?php echo e($performer->stage_name); ?></h2>
                        <?php if($performer->is_verified_badge): ?>
                            <span class="profile-verified-pill">
                                <i class="fas fa-circle-check me-1"></i> Verified
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if($subtitle): ?>
                        <p class="text-muted mb-0 performer-profile-subtitle"><?php echo e($subtitle); ?></p>
                    <?php endif; ?>
                    <?php if($bookingUrl || $onboardingRoute): ?>
                        <div class="profile-booking-bar d-flex flex-wrap align-items-center gap-2 mt-3">
                            <?php if($performer->rate): ?>
                                <span class="profile-rate-pill">
                                    ₱<?php echo e(number_format($performer->rate, 2)); ?>

                                    <span class="profile-rate-suffix">/ event</span>
                                </span>
                            <?php endif; ?>
                            <?php if($onboardingRoute): ?>
                                <a href="<?php echo e($onboardingRoute); ?>" class="btn ph-btn-primary btn-sm">
                                    <i class="fas fa-lock me-1"></i> Complete sign-up to book
                                </a>
                            <?php elseif($bookingUrl): ?>
                                <a href="<?php echo e($bookingUrl); ?>" class="btn ph-btn-primary btn-sm">
                                    Send Booking Request
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php if($rating > 0): ?>
                        <span class="profile-rating-pill">
                            <i class="fas fa-star me-1"></i> <?php echo e(number_format($rating, 1)); ?>

                        </span>
                    <?php endif; ?>
                </div>
            </div>

                <p class="performer-profile-bio mb-0">
                    <?php echo e($performer->bio ?: 'Add a bio to tell organizers about your experience and style.'); ?>

                </p>
            </div>
        </div>

        <?php if(count($tags)): ?>
            <div class="performer-profile-tags mt-4">
                <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="profile-tag"><?php echo e($tag); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/partials/performer-profile-header.blade.php ENDPATH**/ ?>