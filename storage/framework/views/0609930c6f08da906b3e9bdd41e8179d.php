<?php $__env->startSection('title', 'Performer Dashboard'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('performer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.onboarding-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Welcome, <?php echo e($profile?->stage_name ?? auth()->user()->name); ?></h2>
        <p class="text-muted mb-0">
            <?php if($profile?->is_verified_badge): ?>
                <span class="verified-badge"><i class="fas fa-circle-check"></i> Verified Performer</span>
            <?php else: ?>
                <?php if(auth()->user()->hasLimitedAccess()): ?>
                    <span class="text-warning"><i class="fas fa-lock me-1"></i> Limited access — complete sign-up to get verified.</span>
                <?php else: ?>
                    Complete your profile to get verified.
                <?php endif; ?>
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="ph-card p-4 stat-card">
            <h3 class="fw-bold mb-0"><?php echo e($pendingBookings); ?></h3>
            <p class="text-muted mb-0 small">Pending Requests</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ph-card p-4 stat-card">
            <h3 class="fw-bold mb-0"><?php echo e($upcomingBookings); ?></h3>
            <p class="text-muted mb-0 small">Upcoming Bookings</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ph-card p-4 stat-card">
            <h3 class="fw-bold mb-0"><?php echo e($reviews->count()); ?></h3>
            <p class="text-muted mb-0 small">Recent Reviews</p>
        </div>
    </div>
</div>
<div class="event-feed-section mt-4">
    <?php if($availableEvents->isNotEmpty()): ?>
        <div class="event-feed-center">
            <?php $__currentLoopData = $availableEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('partials.event-feed-post', [
                    'event' => $event,
                    'applicationStatus' => $applicationStatuses[$event->id] ?? null,
                    'bookingUrl' => $pendingBookingUrls[$event->id] ?? null,
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="event-feed-center">
            <div class="event-feed-empty text-muted text-center">
                No posts yet.
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\performer\dashboard.blade.php ENDPATH**/ ?>