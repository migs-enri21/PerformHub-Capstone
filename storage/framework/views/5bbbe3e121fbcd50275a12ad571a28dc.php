<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'organizer',
    'editable' => false,
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
    'organizer',
    'editable' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $photoUrl = $organizer->profilePhotoUrl()
        ?? 'https://ui-avatars.com/api/?name='.urlencode($organizer->organization_name).'&background=6346ff&color=fff&size=256';
    $bannerStyle = $organizer->bannerPhotoUrl()
        ? "background-image: url('".$organizer->bannerPhotoUrl()."'); background-position: center ".($organizer->banner_position_y ?? 50)."%;"
        : '';
    $subtitle = collect([$organizer->organization_type ? ucfirst($organizer->organization_type) : null, $organizer->shortLocation()])->filter()->implode(' · ');
?>

<div class="performer-profile-card ph-card mb-4">
    <div class="performer-profile-banner" style="<?php echo e($bannerStyle); ?>">
        <?php if($editable): ?>
            <a href="<?php echo e(route('organizer.profile.edit')); ?>#banner" class="btn btn-sm performer-profile-banner-edit">
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
                    onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?php echo e(urlencode($organizer->organization_name)); ?>&background=6346ff&color=fff&size=256';"
                >
                <?php if($editable): ?>
                    <a href="<?php echo e(route('organizer.profile.edit')); ?>#photo" class="performer-profile-avatar-edit" aria-label="Edit profile photo">
                        <i class="fas fa-camera"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="flex-grow-1 min-w-0">
            <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 mb-2">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h2 class="fw-bold mb-0 performer-profile-name"><?php echo e($organizer->organization_name); ?></h2>
                        <?php if(optional($organizer->user)->is_verified): ?>
                            <span class="profile-verified-pill">
                                <i class="fas fa-circle-check me-1"></i> Verified
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if($subtitle): ?>
                        <p class="text-muted mb-0 performer-profile-subtitle"><?php echo e($subtitle); ?></p>
                    <?php endif; ?>
                </div>
            </div>

                <p class="performer-profile-bio mb-0">
                    <?php echo e($organizer->bio ?: 'Add a bio to tell performers about your organization.'); ?>

                </p>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\partials\organizer-profile-header.blade.php ENDPATH**/ ?>