<?php $__env->startSection('title', 'Organizer Dashboard'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.onboarding-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">Home</h2>
        <p class="text-muted mb-0">Manage your events and discover talent.</p>
    </div>
    <a href="<?php echo e(route('organizer.events.create')); ?>" class="btn ph-btn-primary"><i class="fas fa-plus me-2"></i>Create Event</a>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="org-panel mb-4">
            <h5 class="fw-bold mb-3">Quick Overview</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="<?php echo e(route('organizer.events.index')); ?>" class="org-stat">
                        <i class="fas fa-calendar-plus"></i>
                        <div><strong><?php echo e($upcomingEvents->count()); ?></strong><small>Upcoming Events</small></div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?php echo e(route('organizer.bookings.index')); ?>" class="org-stat">
                        <i class="fas fa-clock"></i>
                        <div><strong><?php echo e($pendingBookings); ?></strong><small>Pending Bookings</small></div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="<?php echo e(route('organizer.bookings.index')); ?>" class="org-stat">
                        <i class="fas fa-check-circle"></i>
                        <div><strong><?php echo e($activeBookings); ?></strong><small>Confirmed Bookings</small></div>
                    </a>
                </div>
            </div>
        </div>

        <div class="org-panel mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Suggested Performers</h5>
                <a href="<?php echo e(route('organizer.performers.index')); ?>" class="small">Browse all</a>
            </div>

            <?php $__empty_1 = true; $__currentLoopData = $recommendedPerformers->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $performer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('organizer.performers.show', $performer)); ?>" class="org-list-item">
                    <img src="<?php echo e($performer->profilePhotoUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($performer->stage_name).'&background=6d3df5&color=fff'); ?>" alt="<?php echo e($performer->stage_name); ?>" class="rounded-circle" width="48" height="48">
                    <div class="flex-grow-1">
                        <strong><?php echo e($performer->stage_name); ?></strong>
                        <?php if($performer->is_verified): ?>
                            <span class="badge bg-success ms-1">Verified</span>
                        <?php endif; ?>
                        <small class="text-muted d-block"><?php echo e($performer->categoryNames() ?: 'Performer'); ?></small>
                        <?php if($performer->portfolios->count()): ?>
                            <small class="text-primary"><?php echo e($performer->portfolios->count()); ?> portfolio <?php echo e(Str::plural('item', $performer->portfolios->count())); ?></small>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted mb-0">No performer recommendations yet.</p>
            <?php endif; ?>
        </div>

        <section class="mb-4">
            <h5 class="fw-bold mb-3">Activity Feed</h5>

            <div class="portfolio-feed-stream">
                <?php $__empty_1 = true; $__currentLoopData = $feedPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if($post['type'] === 'event'): ?>
                        <?php echo $__env->make('partials.event-activity-post', ['event' => $post['event']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php else: ?>
                        <?php echo $__env->make('partials.portfolio-feed-post', [
                            'items' => $post['items'],
                            'performer' => $post['performer']
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="org-panel text-center text-muted">No activity yet.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <aside class="col-xl-4">
        <div class="org-right-column">
            <div class="org-panel mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Up Next</h6>
                    <a href="<?php echo e(route('organizer.events.index')); ?>" class="small">See all</a>
                </div>

                <?php if($upcomingEvents->isNotEmpty()): ?>
                    <?php ($nextEvent = $upcomingEvents->first()); ?>
                    <a href="<?php echo e(route('organizer.events.show', $nextEvent)); ?>" class="org-list-item">
                        <span class="org-event-date"><?php echo e(\Illuminate\Support\Carbon::parse($nextEvent->event_date)->format('d M')); ?></span>
                        <div>
                            <strong><?php echo e($nextEvent->title); ?></strong>
                            <small class="text-muted d-block"><?php echo e($nextEvent->venue); ?></small>
                        </div>
                    </a>
                <?php else: ?>
                    <p class="text-muted small mb-0">No upcoming events yet.</p>
                <?php endif; ?>
            </div>

            <div class="org-panel mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0">Notifications</h6>
                    <a href="<?php echo e(route('notifications.index')); ?>" class="small">See all</a>
                </div>

                <?php $__empty_1 = true; $__currentLoopData = $recentNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e($notification->link ?: route('notifications.index')); ?>" class="org-list-item">
                        <i class="fas fa-bell text-primary"></i>
                        <div>
                            <strong><?php echo e($notification->title); ?></strong>
                            <small class="text-muted d-block"><?php echo e($notification->message); ?></small>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted small mb-0">No new notifications.</p>
                <?php endif; ?>
            </div>
        </div>
    </aside>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\organizer\dashboard.blade.php ENDPATH**/ ?>