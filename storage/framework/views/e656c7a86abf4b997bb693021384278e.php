<?php $__env->startSection('title', 'Event Details'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-3">
    <a href="<?php echo e(route('admin.events.index')); ?>" class="btn btn-outline-secondary">← Back to Events</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ph-card p-4">
            <h2 class="fw-bold mb-2"><?php echo e($booking->event_name); ?></h2>
            <p class="text-muted mb-3">Event details and booking information.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Organizer</div>
                    <div class="fw-semibold"><?php echo e($booking->organizer?->fullName() ?? '—'); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Performer</div>
                    <div class="fw-semibold"><?php echo e($booking->performer?->fullName() ?? '—'); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Event Date</div>
                    <div class="fw-semibold"><?php echo e($booking->event_date->format('F d, Y')); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Event Time</div>
                    <div class="fw-semibold"><?php echo e($booking->event_time ? \Carbon\Carbon::parse($booking->event_time)->format('g:i A') : '—'); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Venue</div>
                    <div class="fw-semibold"><?php echo e($booking->venue ?? '—'); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Duration</div>
                    <div class="fw-semibold"><?php echo e($booking->duration_hours ? $booking->duration_hours . ' hr' : '—'); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Status</div>
                    <span class="badge <?php echo e($booking->statusBadgeClass()); ?>"><?php echo e($booking->statusLabel()); ?></span>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Created</div>
                    <div class="fw-semibold"><?php echo e(optional($booking->created_at)->format('F d, Y h:i A') ?? '—'); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Last Updated</div>
                    <div class="fw-semibold"><?php echo e(optional($booking->updated_at)->format('F d, Y h:i A') ?? '—'); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Contract</div>
                    <div class="fw-semibold"><?php echo e($booking->contractStatusLabel()); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Contract File</div>
                    <div class="fw-semibold">
                        <?php if($booking->contractUrl()): ?>
                            <a href="<?php echo e($booking->contractUrl()); ?>" target="_blank" rel="noopener">Download contract</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Requirements</div>
                    <div class="fw-semibold"><?php echo e($booking->requirements ?? '—'); ?></div>
                </div>
                <div class="col-12">
                    <div class="small text-muted">Notes</div>
                    <div class="fw-semibold"><?php echo e($booking->notes ?? '—'); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ph-card p-4">
            <h5 class="fw-bold mb-3">Event Summary</h5>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Name</span><span><?php echo e($booking->event_name); ?></span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Date Created</span><span><?php echo e($booking->created_at->format('M d, Y')); ?></span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Scheduled Date</span><span><?php echo e($booking->event_date->format('M d, Y')); ?></span></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Created By</span><span><?php echo e($booking->organizer?->fullName() ?? '—'); ?></span></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\admin\events\show.blade.php ENDPATH**/ ?>