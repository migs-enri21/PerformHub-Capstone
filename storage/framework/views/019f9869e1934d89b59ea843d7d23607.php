<?php $__env->startSection('title', 'Organizer Dashboard'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('partials.onboarding-banner', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold mb-1">

            Home

        </h2>

        <p class="text-muted mb-0">

            Stay updated with your events and performer activities.

        </p>

    </div>

    <a href="<?php echo e(route('organizer.events.create')); ?>" class="btn ph-btn-primary">

        <i class="fas fa-plus me-2"></i>

        Create Event

    </a>

</div>
<p class="text-muted mb-4">
    <?php if(auth()->user()->hasLimitedAccess()): ?>
        Manage your events and discover talent — complete sign-up to book performers.
    <?php else: ?>
        Manage your events and discover talent
    <?php endif; ?>
</p>

<div class="ph-card p-4 mb-4">

    <h5 class="fw-bold mb-4">

    Recent Activity

    </h5>

<div class="text-center py-5">

    <i class="fas fa-stream fa-3x text-muted mb-3"></i>

    <h5 class="fw-bold">

        No Recent Activity

    </h5>

    <p class="text-muted mb-0">

        Performer portfolio uploads and profile updates will appear here.

    </p>

    </div>
</div>

<h4 class="fw-bold mb-3">Featured Performers</h4>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-semibold mb-3">Suggested for you</h5>
    <div class="row g-3">
        <?php $__empty_1 = true; $__currentLoopData = $recommendedPerformers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:var(--ph-bg-input);">
                    <img src="<?php echo e($p->profilePhotoUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($p->stage_name).'&background=6346ff&color=fff'); ?>" class="rounded-circle" width="48" height="48" style="object-fit:cover;">
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?php echo e($p->stage_name); ?></h6>
                        <small class="text-muted"><?php echo e($p->categoryNames()); ?></small>
                    </div>
                    <a href="<?php echo e(route('organizer.performers.show', $p)); ?>" class="btn btn-sm ph-btn-primary">View</a>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-muted mb-0">No recommendations available.</p>
            <div class="mt-4">

    <a href="<?php echo e(route('organizer.performers.index')); ?>"
       class="btn ph-btn-primary">

        Browse All Performers

    </a>

</div>
        <?php endif; ?>
    </div>
</div>

<div class="ph-card p-4 mt-4">

    <div class="ph-card p-4 mt-4">

    <h5 class="fw-bold mb-3">

        Organizer Calendar

    </h5>

    <div class="text-center py-4">

        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>

        <p class="text-muted mb-0">

            Your scheduled events will appear here once the calendar feature is added.

        </p>

    </div>

</div>

<div class="ph-card p-4 mt-4">

    <h5 class="fw-bold mb-3">

        Upcoming Reminder

    </h5>


    <?php if($myEvents->isNotEmpty()): ?>

        <p class="mb-0">

            Your next scheduled event is

            <strong><?php echo e($myEvents->first()->title); ?></strong>

            on

            <strong><?php echo e(\Carbon\Carbon::parse($myEvents->first()->event_date)->format('F d, Y')); ?></strong>.

        </p>

    <?php else: ?>

        <p class="text-muted mb-0">

            You don't have any upcoming events yet. Create one to get started.

        </p>

    <?php endif; ?>

</div>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/organizer/dashboard.blade.php ENDPATH**/ ?>