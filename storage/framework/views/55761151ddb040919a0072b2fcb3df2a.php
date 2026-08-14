<?php $__env->startSection('title', $event->title); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><?php echo e($event->title); ?></h2>
            <p class="text-muted mb-0"><?php echo e(ucfirst($event->status)); ?> event</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('organizer.events.edit', $event)); ?>" class="btn ph-btn-primary btn-sm">Edit</a>
            <a href="<?php echo e(route('organizer.events.index')); ?>" class="btn ph-btn-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="ph-card p-0 overflow-hidden">
        <?php if($event->photos->count() > 1): ?>
            <?php echo $__env->make('partials.event-photo-collage', ['photos' => $event->photos, 'title' => $event->title], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($event->photos->count() === 1): ?>
            <div class="organizer-event-cover">
                <img src="<?php echo e($event->photos->first()->fileUrl()); ?>" alt="<?php echo e($event->title); ?>">
            </div>
        <?php elseif($event->coverPhotoUrl()): ?>
            <div class="organizer-event-cover">
                <img src="<?php echo e($event->coverPhotoUrl()); ?>" alt="<?php echo e($event->title); ?>">
            </div>
        <?php endif; ?>

        <div class="p-4">
        <?php if($event->description): ?>
            <p class="text-muted"><?php echo e($event->description); ?></p>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-6">
                <strong class="event-detail-label d-block mb-1">Date</strong>
                <span class="text-muted"><?php echo e(\Illuminate\Support\Carbon::parse($event->event_date)->format('F j, Y')); ?></span>
            </div>
            <div class="col-md-6">
                <strong class="event-detail-label d-block mb-1">Time</strong>
                <span class="text-muted">
                    <?php echo e(\Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A')); ?>

                    <?php if($event->end_time): ?>
                        – <?php echo e(\Illuminate\Support\Carbon::parse($event->end_time)->format('g:i A')); ?>

                    <?php endif; ?>
                </span>
            </div>
            <div class="col-md-6">
                <strong class="event-detail-label d-block mb-1">Venue</strong>
                <span class="text-muted"><?php echo e($event->venue); ?></span>
            </div>
            <div class="col-md-6">
                <strong class="event-detail-label d-block mb-1">Event Type</strong>
                <span class="text-muted"><?php echo e($event->eventType?->name ?? '—'); ?></span>
            </div>
            <?php if($event->preferredCategory): ?>
                <div class="col-md-6">
                    <strong class="event-detail-label d-block mb-1">Preferred Category</strong>
                    <span class="text-muted"><?php echo e($event->preferredCategory->name); ?></span>
                </div>
            <?php endif; ?>
            <?php if($event->budget): ?>
                <div class="col-md-6">
                    <strong class="event-detail-label d-block mb-1">Budget</strong>
                    <span class="text-muted">₱<?php echo e(number_format((float) $event->budget, 0)); ?></span>
                </div>
            <?php endif; ?>

            </div>
        </div>
    </div>
    <hr class="my-4">

    <h3 class="mb-3">Applicants (<?php echo e($event->applications->count()); ?>)</h3>

    <?php $__empty_1 = true; $__currentLoopData = $event->applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

    <div class="ph-card p-3 mb-3">

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">
                <?php echo e($application->performer->performerProfile->stage_name ?? $application->performer->name); ?>

            </h5>

            <span class="badge
                <?php if($application->status == 'pending'): ?>
                bg-warning
                <?php elseif($application->status == 'invited'): ?>
                bg-info
                <?php elseif($application->status == 'accepted'): ?>
                bg-success
                <?php elseif($application->status == 'declined'): ?>
                bg-danger
                <?php endif; ?>">
                <?php echo e(ucfirst($application->status)); ?>

            </span>

        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if($application->status === 'pending'): ?>
                <a href="<?php echo e(route('organizer.bookings.create', ['performer' => $application->performer->performerProfile, 'event' => $event->id])); ?>" class="btn ph-btn-primary btn-sm">
                    Accept & Send Booking
                </a>
                <form method="POST" action="<?php echo e(route('organizer.events.applications.decline', [$event, $application])); ?>" onsubmit="return confirm('Decline this applicant?');">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">Decline</button>
                </form>
            <?php elseif($application->status === 'invited'): ?>
                <span class="text-muted small">Booking request sent — waiting for performer</span>
           <?php elseif($application->status === 'accepted' && isset($bookings[$application->performer_id])): ?><div class="d-flex align-items-center justify-content-between">
                <span class="text-success">✓ Accepted & Booked</span>
            <a href="<?php echo e(route('organizer.bookings.show', $bookings[$application->performer_id])); ?>"class="btn ph-btn-primary btn-sm">View Booking</a></div>
            <?php elseif($application->status === 'declined'): ?>
                <span class="text-muted small">Declined</span>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

<div class="alert alert-secondary"> No performers have applied yet.</div>

<?php endif; ?>    
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\organizer\events\show.blade.php ENDPATH**/ ?>