<?php $__env->startSection('title', 'My Events'); ?>

<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Events</h2>
        <p class="text-muted mb-0">Manage all of your events in one place.</p>
    </div>

    <a href="<?php echo e(route('organizer.events.create')); ?>" class="btn ph-btn-primary">
        <i class="fas fa-plus me-2"></i>
        Create Event
    </a>
</div>

<div class="mb-4">
    <a href="<?php echo e(route('organizer.events.index')); ?>" class="btn btn-outline-primary btn-sm me-2">All</a>
    <a href="<?php echo e(route('organizer.events.index', ['status' => 'upcoming'])); ?>" class="btn btn-outline-secondary btn-sm me-2">Upcoming</a>
    <a href="<?php echo e(route('organizer.events.index', ['status' => 'ongoing'])); ?>" class="btn btn-outline-secondary btn-sm me-2">Ongoing</a>
    <a href="<?php echo e(route('organizer.events.index', ['status' => 'completed'])); ?>" class="btn btn-outline-secondary btn-sm me-2">Completed</a>
    <a href="<?php echo e(route('organizer.events.index', ['status' => 'cancelled'])); ?>" class="btn btn-outline-secondary btn-sm">Cancelled</a>
</div>

<?php if($events->isEmpty()): ?>
    <div class="alert alert-info">You haven't created any events yet.</div>
<?php else: ?>
    <div class="row g-4">
        <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-lg-6">
                <div class="ph-card organizer-event-card h-100 overflow-hidden">
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
                    <?php else: ?>
                        <div class="organizer-event-cover organizer-event-cover--empty">
                            <span class="text-muted">No Event Photo</span>
                        </div>
                    <?php endif; ?>

                    <div class="p-3">
                        <h5 class="fw-bold text-white mb-2"><?php echo e($event->title); ?></h5>

                        <?php if($event->description): ?>
                            <p class="text-muted small mb-3"><?php echo e($event->description); ?></p>
                        <?php endif; ?>

                        <p class="text-muted mb-2 small">
                            <i class="fas fa-calendar me-2"></i>
                            <?php echo e(\Carbon\Carbon::parse($event->event_date)->format('F d, Y')); ?>

                        </p>

                        <p class="text-muted mb-3 small">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo e($event->venue); ?>

                        </p>

                        <?php if(in_array(strtolower($event->status), ['open', 'upcoming'], true)): ?>
                            <span class="badge bg-primary">
                        <?php elseif($event->status == 'ongoing'): ?>
                            <span class="badge bg-success">
                        <?php elseif(in_array(strtolower($event->status), ['completed'], true)): ?>
                            <span class="badge bg-dark">
                        <?php elseif(in_array(strtolower($event->status), ['cancelled'], true)): ?>
                            <span class="badge bg-danger">
                        <?php else: ?>
                            <span class="badge bg-secondary">
                        <?php endif; ?>
                            <?php echo e(ucfirst($event->status)); ?>

                        </span>

                        <div class="mt-3 d-flex flex-wrap gap-2">
                            <a href="<?php echo e(route('organizer.events.show', $event)); ?>" class="btn btn-outline-primary btn-sm">View</a>
                            <a href="<?php echo e(route('organizer.events.edit', $event)); ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                            <form method="POST" action="<?php echo e(route('organizer.events.destroy', $event)); ?>" onsubmit="return confirm('Delete this event permanently?');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/organizer/events/index.blade.php ENDPATH**/ ?>