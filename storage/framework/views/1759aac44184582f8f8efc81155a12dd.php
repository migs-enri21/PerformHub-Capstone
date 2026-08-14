<?php $__env->startSection('title', 'Find Performers'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<h2 class="fw-bold mb-4">Search Performers</h2>

<?php if($selectedEvent): ?>
<div class="alert alert-info mb-4">
    <strong>Recommended performers for</strong>
    <?php echo e($selectedEvent->title); ?>


    <br>

    <small>
        <strong>Category:</strong>
        <?php echo e($selectedEvent->eventType->name ?? 'N/A'); ?>

    </small>

    <br>

    <small>
        <strong>Event Date:</strong>
        <?php echo e(\Carbon\Carbon::parse($selectedEvent->event_date)->format('F d, Y')); ?>

    </small>

    <br>

    <small class="text-muted">Recommendations are based on the selected event's category and performer availability.</small>
</div>
<?php endif; ?>

<div class="ph-card p-4 mb-4">
    <form method="GET" class="row g-3">
        <div class="col-md-3"><input type="text" name="search" class="form-control ph-input" placeholder="Search..." value="<?php echo e(request('search')); ?>"></div>
        <div class="col-md-2">
            <select name="category_id" class="form-select ph-input">
                <option value="">All Categories</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(request('category_id')==$c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-2"><?php echo $__env->make('partials.genre-select', ['value' => request('genre'), 'placeholder' => 'All Genres'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
        <div class="col-md-2"><input type="number" name="min_rating" class="form-control ph-input" placeholder="Min Rating" min="1" max="5" value="<?php echo e(request('min_rating')); ?>"></div>
        <div class="col-md-2"><input type="date" name="available_date" class="form-control ph-input" value="<?php echo e(request('available_date')); ?>"></div>
        <div class="col-md-1"><button class="btn ph-btn-primary w-100">Filter</button></div>
    </form>
</div>

<div class="row g-4">
    <?php $__empty_1 = true; $__currentLoopData = $performers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-6 col-lg-4">
            <div class="ph-card p-4 h-100">
                <div class="d-flex gap-3 mb-3">
                    <img src="<?php echo e($p->profilePhotoUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($p->stage_name).'&background=6346ff&color=fff'); ?>" class="performer-avatar" alt="">
                    <div>
                        <h6 class="mb-0"><?php echo e($p->stage_name); ?> <?php if($p->is_verified_badge): ?><i class="fas fa-circle-check verified-badge"></i><?php endif; ?></h6>
                        <small class="text-muted"><?php echo e($p->categoryNames()); ?> · <?php echo e($p->genre); ?></small>
                        <div class="text-warning small"><?php for($i=0;$i<round($p->averageRating());$i++): ?><i class="fas fa-star"></i><?php endfor; ?></div>
                    </div>
                </div>
                <p class="text-muted small"><?php echo e(Str::limit($p->bio, 80)); ?></p>
                <a href="<?php echo e(route('organizer.performers.show', $p)); ?>" class="btn ph-btn-primary btn-sm">View Profile</a>
                <a href="<?php echo e(route('organizer.bookings.create', $p)); ?>"
   class="btn ph-btn-primary btn-sm flex-fill">
    Send Booking Request
</a>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12 text-muted">No performers found.</div>
    <?php endif; ?>
</div>
<?php echo e($performers->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\organizer\performers\index.blade.php ENDPATH**/ ?>