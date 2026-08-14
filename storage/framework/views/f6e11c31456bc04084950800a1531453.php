<?php $__env->startSection('title', $booking->event_name); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('organizer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1"><?php echo e($booking->event_name); ?></h2>
        <span class="badge <?php echo e($booking->statusBadgeClass()); ?>"><?php echo e($booking->statusLabel()); ?></span>
    </div>
    <a href="<?php echo e(route('organizer.bookings.index')); ?>" class="btn ph-btn-outline btn-sm">Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="ph-card p-4 mb-4">
            <p><strong>Performer:</strong> <?php echo e($booking->performer->performerProfile?->stage_name); ?></p>
            <p><strong>Date:</strong> <?php echo e($booking->event_date->format('F d, Y')); ?></p>
            <p><strong>Venue:</strong> <?php echo e($booking->venue ?? 'TBD'); ?></p>
            <p><strong>Requirements:</strong> <?php echo e($booking->requirements ?? 'None'); ?></p>
        </div>
        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-1">Contract Management</h5>
            <?php if($booking->status === 'pending'): ?>
                <p class="text-muted small mb-3">Upload the contract now. The performer can review it after accepting the booking.</p>
            <?php else: ?>
                <p class="text-muted small mb-3">Upload the contract for the performer to review and confirm.</p>
            <?php endif; ?>

            <?php if($booking->hasContract()): ?>
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <a href="<?php echo e($booking->contractUrl()); ?>" target="_blank" class="btn ph-btn-outline btn-sm">View Contract File</a>
                    <?php if($booking->hasSignedContract()): ?>
                        <a href="<?php echo e($booking->signedContractUrl()); ?>" target="_blank" class="btn ph-btn-outline btn-sm">View Signed Contract</a>
                    <?php endif; ?>
                    <span class="badge <?php echo e($booking->contractStatusBadgeClass()); ?>"><?php echo e($booking->contractStatusLabel()); ?></span>
                </div>
                <?php if($booking->performer_confirmed_contract): ?>
                    <p class="text-success small mb-3">Performer confirmed on <?php echo e($booking->contract_confirmed_at->format('M d, Y g:i A')); ?>.</p>
                <?php elseif($booking->status === 'accepted'): ?>
                    <?php if($booking->hasSignedContract()): ?>
                        <p class="text-warning small mb-3">Signed copy received. Waiting for the performer to confirm.</p>
                    <?php else: ?>
                        <p class="text-warning small mb-3">Waiting for the performer to upload a signed copy and confirm.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted small mb-3">Contract saved. It will be available after the performer accepts.</p>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('organizer.bookings.contract', $booking)); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="file" name="contract" class="form-control ph-input mb-2" accept=".pdf,.jpg,.jpeg,.png" <?php echo e($booking->hasContract() ? '' : 'required'); ?>>
                <small class="text-muted d-block mb-2">Accepted files: PDF, JPG, JPEG, or PNG. Maximum 10 MB.</small>
                <button class="btn ph-btn-primary btn-sm"><?php echo e($booking->hasContract() ? 'Replace Contract' : 'Upload Contract'); ?></button>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ph-card p-4 mb-4">
            <h5 class="fw-semibold mb-3">Booking Progress</h5>

            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-success">Done</span>
                <span>Booking Request Sent</span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3">
                <?php if($booking->status === 'accepted' || $booking->status === 'completed'): ?>
                    <span class="badge bg-success">Done</span>
                <?php elseif($booking->status === 'pending'): ?>
                    <span class="badge bg-warning text-dark">Current</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Pending</span>
                <?php endif; ?>
                <span>Performer Accepted</span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3">
                <?php if($booking->hasContract()): ?>
                    <span class="badge bg-success">Done</span>
                <?php elseif($booking->status === 'accepted'): ?>
                    <span class="badge bg-warning text-dark">Current</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Pending</span>
                <?php endif; ?>
                <span>Contract Uploaded</span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3">
                <?php if($booking->hasSignedContract()): ?>
                    <span class="badge bg-success">Done</span>
                <?php elseif($booking->hasContract()): ?>
                    <span class="badge bg-warning text-dark">Current</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Pending</span>
                <?php endif; ?>
                <span>Signed Copy Uploaded</span>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3">
                <?php if($booking->performer_confirmed_contract): ?>
                    <span class="badge bg-success">Done</span>
                <?php elseif($booking->hasSignedContract()): ?>
                    <span class="badge bg-warning text-dark">Current</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Pending</span>
                <?php endif; ?>
                <span>Contract Confirmed</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if($booking->status === 'completed'): ?>
                    <span class="badge bg-success">Done</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Pending</span>
                <?php endif; ?>
                <span>Booking Completed</span>
            </div>
        </div>

        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-3">Actions</h5>
            <?php if($booking->status === 'accepted'): ?>
                <?php if($booking->hasContract() && ! $booking->performer_confirmed_contract): ?>
                    <p class="text-warning small mb-2">The performer must confirm the contract before you can mark this booking complete.</p>
                <?php endif; ?>
                <form method="POST" action="<?php echo e(route('organizer.bookings.complete', $booking)); ?>"><?php echo csrf_field(); ?><button class="btn btn-success w-100" <?php if($booking->hasContract() && ! $booking->performer_confirmed_contract): echo 'disabled'; endif; ?>>Mark Completed</button></form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\organizer\bookings\show.blade.php ENDPATH**/ ?>