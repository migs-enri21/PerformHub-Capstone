<?php $__env->startSection('title', 'Bookings'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('performer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<h2 class="fw-bold mb-4">Booking History</h2>






<div class="ph-card p-0 overflow-hidden">
    <table class="table table-dark table-hover mb-0">
        <thead><tr><th>Event</th><th>Organizer</th><th>Date</th><th>Status</th><th>Contract</th><th></th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($booking->event_name); ?></td>
                    <td><?php echo e($booking->organizer->organizerProfile?->organization_name ?? $booking->organizer->name); ?></td>
                    <td><?php echo e($booking->event_date->format('M d, Y')); ?></td>
                    <td><span class="badge <?php echo e($booking->statusBadgeClass()); ?>"><?php echo e($booking->statusLabel()); ?></span></td>
                    <td>
                        <?php if($booking->status === 'accepted' || $booking->hasContract()): ?>
                            <span class="badge <?php echo e($booking->contractStatusBadgeClass()); ?>"><?php echo e($booking->contractStatusLabel(true)); ?></span>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo e(route('performer.bookings.show', $booking)); ?>" 
                        class="btn btn-sm <?php echo e($booking->needsContractReview() ? 'ph-btn-primary' : 'ph-btn-outline'); ?> booking-view-btn">
                            <i class="fas fa-eye me-1"></i> <?php echo e($booking->needsContractReview() ? 'Review' : 'View'); ?> </a>
                        </a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No bookings yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo e($bookings->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/performer/bookings/index.blade.php ENDPATH**/ ?>