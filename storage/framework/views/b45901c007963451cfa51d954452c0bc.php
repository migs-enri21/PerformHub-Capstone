<?php $__env->startSection('title', 'New Booking'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<h2 class="fw-bold mb-4">Book <?php echo e($performer->stage_name); ?></h2>
<form method="POST" action="<?php echo e(route('organizer.bookings.store', $performer)); ?>">
    <?php echo csrf_field(); ?>
    <div class="ph-card p-4">
        <div class="row g-3">
            <div class="mb-4">

    <label class="form-label">Select Existing Event</label>

    <select id="eventSelector"class="form-select ph-input">

        <option value=""> -- Select an Event --</option>

        <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <option
                value="<?php echo e($event->id); ?>"
                data-title="<?php echo e($event->title); ?>"
                data-date="<?php echo e($event->event_date); ?>"
                data-time="<?php echo e($event->start_time); ?>"
                data-venue="<?php echo e($event->venue); ?>"
                data-requirements="<?php echo e($event->requirements); ?>">

                <?php echo e($event->title); ?>


            </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </select></div>

            <div class="col-md-6"><label class="form-label text-muted small">Event Name</label><input type="text" name="event_name"class="form-control ph-input" id="event_name" value="<?php echo e(old('event_name')); ?>"required></div>
            <div class="col-md-3"><label class="form-label text-muted small">Event Date</label><input type="date" name="event_date"class="form-control ph-input" id="event_date" value="<?php echo e(old('event_date')); ?>"required></div>
            <div class="col-md-3"><label class="form-label text-muted small">Event Time</label><input type="time" name="event_time" class="form-control ph-input"id="event_time" value="<?php echo e(old('event_time')); ?>"required></div>
            <div class="col-md-6"><label class="form-label text-muted small">Venue</label><input type="text" name="venue" class="form-control ph-input" id="venue" value="<?php echo e(old('venue')); ?>"required></div>
            <div class="col-md-3"><label class="form-label text-muted small">Duration (hours)</label><input type="number" name="duration_hours" class="form-control ph-input" min="1" max="24"></div>
            <div class="col-12"><label class="form-label text-muted small">Requirements</label><textarea id="requirements" name="requirements" class="form-control ph-input"><?php echo e(old('requirements')); ?></textarea></div>
            <div class="col-12"><label class="form-label text-muted small">Notes</label><textarea name="notes" class="form-control ph-input" rows="2"></textarea></div>

        </div>
        <button type="submit" class="btn ph-btn-primary mt-4">Send Booking Request</button>
    </div>
</form>

    <script>
    document.addEventListener('DOMContentLoaded', () => {

    const selector = document.getElementById('eventSelector');

    selector.addEventListener('change', function () {

        const selected = this.options[this.selectedIndex];

        document.getElementById('event_name').value = selected.dataset.title || '';
        document.getElementById('event_date').value = selected.dataset.date || '';
        document.getElementById('event_time').value = selected.dataset.time || '';
        document.getElementById('venue').value = selected.dataset.venue || '';
        document.getElementById('requirements').value = selected.dataset.requirements || '';
        });

    });
    </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/organizer/bookings/create.blade.php ENDPATH**/ ?>