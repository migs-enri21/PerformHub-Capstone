<?php $__env->startSection('title', 'My Profile'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('performer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-end mb-2">
    <a href="<?php echo e(route('performer.profile.edit')); ?>" class="btn ph-btn-outline btn-sm">
        <i class="fas fa-pen me-1"></i> Edit Profile
    </a>
</div>

<?php echo $__env->make('partials.performer-profile-header', [
    'performer' => $profile,
    'editable' => true,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-semibold mb-3">Booking Rate</h5>
            <?php if($profile->rate): ?>
                <p class="fw-semibold mb-0 fs-5">₱<?php echo e(number_format($profile->rate, 2)); ?> <span class="text-muted small fw-normal">/ event</span></p>
            <?php else: ?>
                <p class="text-muted mb-0">No rate set yet. <a href="<?php echo e(route('performer.profile.edit')); ?>">Add your rate</a>.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-semibold mb-3">Location</h5>
            <p class="text-muted mb-0"><?php echo e($profile->fullLocation() ?: 'No location set yet.'); ?></p>
        </div>
    </div>
    <?php if($profile->socialLinks()): ?>
        <div class="col-12">
            <?php echo $__env->make('partials.social-media-section', ['performer' => $profile, 'editable' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?>
    <div class="col-12" id="availability">
        <div class="ph-card p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-semibold mb-0">Availability Calendar</h5>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php if(! auth()->user()->hasLimitedAccess()): ?>
                        <?php if($profile->google_calendar_connected): ?>
                            <form method="POST" action="<?php echo e(route('performer.google-calendar.sync')); ?>" class="d-inline m-0">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm ph-btn-outline">
                                    <i class="fab fa-google me-1"></i> Sync Google Calendar
                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('performer.google-calendar.disconnect')); ?>" class="d-inline m-0">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm ph-btn-outline">
                                    Disconnect Google
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?php echo e(route('performer.google-calendar.connect')); ?>" class="btn btn-sm ph-btn-outline">
                                <i class="fab fa-google me-1"></i> Connect Google Calendar
                            </a>
                        <?php endif; ?>
                    <?php elseif(auth()->user()->hasLimitedAccess()): ?>
                        <a href="<?php echo e(auth()->user()->onboardingRoute()); ?>" class="btn btn-sm ph-btn-primary">
                            <i class="fas fa-lock me-1"></i> Complete sign-up to manage
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php if($profile->google_calendar_connected): ?>
                <p class="text-muted small mb-3">
                    Google Calendar connected
                    <?php if($profile->google_calendar_synced_at): ?>
                        · Last synced <?php echo e($profile->google_calendar_synced_at->diffForHumans()); ?>

                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php echo $__env->make('partials.availability-calendar', [
                'schedules' => $calendar['schedules'],
                'bookingCalendar' => $calendar['bookingCalendar'],
                'googleBusy' => $calendar['googleBusy'],
                'editable' => ! auth()->user()->hasLimitedAccess(),
                'storeUrl' => route('performer.availability.store'),
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/performer/profile/show.blade.php ENDPATH**/ ?>