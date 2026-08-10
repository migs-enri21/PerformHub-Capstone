<?php $__env->startSection('title', 'Create Event'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="container">

    <h2 class="fw-bold mb-4">Event Information</h2>

    <div class="ph-card p-4">
        
            <form method="POST" action="<?php echo e(route('organizer.events.store')); ?>" enctype="multipart/form-data">
             <?php echo csrf_field(); ?>

                <div class="mb-4">

                <label class="form-label fw-semibold"> Event Banner Photo</label>

                <input
                type="file"
                name="banner_photo"
                class="form-control"
                accept="image/*">

                <small class="text-muted"> Upload a banner photo for your event.</small>

                </div>
                <div class="mb-3"><label class="form-label">Event Name</label><input type="text" class="form-control" name="title" value="<?php echo e(old('title')); ?>"></div>

                <div class="row">

                    <div class="col-md-6 mb-3"><label class="form-label">Event Type</label><select class="form-select" name="event_type_id" required>
                    <option value="">Select Event Type</option>
                    <?php $__currentLoopData = $eventTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eventType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($eventType->id); ?>" <?php echo e(old('event_type_id') == $eventType->id ? 'selected' : ''); ?>>
                        <?php echo e($eventType->name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    </div>

                    <div class="col-md-6 mb-3"><label class="form-label">Preferred Performer Category</label>

                    <select name="preferred_category_id" class="form-select">

                    <option value="">Select Performer Category</option>

                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category->id); ?>" <?php echo e(old('preferred_category_id') == $category->id ? 'selected' : ''); ?>> <?php echo e($category->name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </select>
                </div>

                </div>

                <div class="row">

                <div class="col-md-4 mb-3"><label class="form-label">Event Date</label><input type="date" class="form-control" name="event_date" value="<?php echo e(old('event_date')); ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Start Time</label><input type="time" class="form-control" name="start_time" value="<?php echo e(old('start_time')); ?>"></div>

                <div class="col-md-4 mb-3"><label class="form-label">End Time</label><input type="time" class="form-control" name="end_time" value="<?php echo e(old('end_time')); ?>"></div>
                </div>

                <div class="mb-3"><label class="form-label">Venue / Location</label><input type="text" class="form-control" name="venue" value="<?php echo e(old('venue')); ?>"></div>

                <div class="row">

                    <div class="col-md-6 mb-3"><label class="form-label">Budget (₱)</label><input type="number" class="form-control" name="budget" value="<?php echo e(old('budget')); ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Number of Performers Needed</label><input type="number" class="form-control" name="performers_needed" min="1" value="<?php echo e(old('performers_needed',1)); ?>"></div>

                </div>

                <div class="mb-4"><label class="form-label">Special Requirements</label><textarea class="form-control" rows="4" name="description"><?php echo e(old('description')); ?></textarea></div>

                <div class="d-flex justify-content-end">

                    <a href="<?php echo e(route('organizer.dashboard')); ?>" class="btn ph-btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn ph-btn-primary">Create Event & Find Performers</button>

                </div>

            </form>

        
    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/organizer/events/create.blade.php ENDPATH**/ ?>