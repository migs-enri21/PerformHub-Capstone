<?php $__env->startSection('title', $performer->stage_name); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.performer-profile-header', [
    'performer' => $performer,
    'editable' => false,
    'bookingUrl' => auth()->user()->hasLimitedAccess()
        ? null
        : route('organizer.bookings.create', [
            'performer' => $performer,
            'event' => request('event'),
        ]),
    'onboardingRoute' => auth()->user()->hasLimitedAccess()
        ? auth()->user()->onboardingRoute()
        : null,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if($performer->socialLinks()): ?>
    <?php echo $__env->make('partials.social-media-section', ['performer' => $performer], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<div class="ph-card p-4 mb-4" id="availability">
    <h5 class="fw-semibold mb-3">Availability Calendar</h5>
    <?php echo $__env->make('partials.availability-calendar', [
        'schedules' => $calendar['schedules'],
        'bookingCalendar' => $calendar['bookingCalendar'],
        'googleBusy' => $calendar['googleBusy'],
        'editable' => false,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php
    $portfolioGroups = $performer->portfolios
        ->sortByDesc('created_at')
        ->groupBy(fn ($item) => \App\Support\PortfolioFeed::groupKey($item))
        ->map(fn ($group) => $group->values());
?>
<?php if($portfolioGroups->isNotEmpty()): ?>
    <div class="ph-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">Portfolio</h5>
        <?php echo $__env->make('partials.portfolio-feed', ['posts' => $portfolioGroups->values()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php endif; ?>

<div class="ph-card p-4">
    <h5 class="fw-semibold mb-3">Reviews</h5>
    <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="mb-3 pb-3 border-bottom border-secondary-subtle">
            <div class="text-warning small mb-1">
                <?php for($i = 0; $i < $r->rating; $i++): ?>
                    <i class="fas fa-star"></i>
                <?php endfor; ?>
            </div>
            <p class="small text-muted mb-0"><?php echo e($r->comment); ?></p>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-muted mb-0">No reviews yet.</p>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\organizer\performers\show.blade.php ENDPATH**/ ?>