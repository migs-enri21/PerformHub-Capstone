<?php $__env->startSection('title', $booking->event_name); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('performer.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1"><?php echo e($booking->event_name); ?></h2>
        <span class="badge <?php echo e($booking->statusBadgeClass()); ?>"><?php echo e($booking->statusLabel()); ?></span>
        <?php if($booking->needsContractReview()): ?>
            <span class="badge bg-warning text-dark ms-1">Contract needs review</span>
        <?php endif; ?>
    </div>
    <?php
        if (request('from') === 'notifications') {
            $backUrl = route('notifications.index');
        } else {
            $backUrl = route('performer.bookings.index');
        }
    ?>

<a href="<?php echo e($backUrl); ?>" class="btn ph-btn-outline btn-sm booking-back-btn">
    <i class="fas fa-arrow-left me-1"></i> Back
</a>
</div>

<div class="row g-4">
    <div class="col-lg-12">
        <?php if($booking->status === 'pending'): ?>
            <div class="ph-card p-4 mb-4">
                <h5 class="fw-semibold mb-3">Respond to Booking</h5>
                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="<?php echo e(route('performer.bookings.accept', $booking)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn ph-btn-primary booking-accept-btn">Accept Booking</button>
                    </form>
                    <form method="POST" action="<?php echo e(route('performer.bookings.reject', $booking)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn ph-btn-outline booking-decline-btn">Decline</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="ph-card p-4 mb-4">
            <h5 class="fw-semibold mb-3">Event Details</h5>
            <p><strong>Date:</strong> <?php echo e($booking->event_date->format('F d, Y')); ?> <?php if($booking->event_time): ?> at <?php echo e(\Carbon\Carbon::parse($booking->event_time)->format('g:i A')); ?><?php endif; ?></p>
            <p><strong>Venue:</strong> <?php echo e($booking->venue ?? 'TBD'); ?></p>
            <?php
                if ($booking->duration_hours) {
                    $durationLabel = $booking->duration_hours.' hours';
                } else {
                    $durationLabel = 'N/A';
                }
            ?>
            <p><strong>Duration:</strong> <?php echo e($durationLabel); ?></p>
            <p><strong>Requirements:</strong> <?php echo e($booking->requirements ?? 'None specified'); ?></p>
            <p class="mb-0"><strong>Organizer:</strong> <?php echo e($booking->organizer->organizerProfile?->organization_name ?? $booking->organizer->name); ?></p>
        </div>

        <?php if($booking->status === 'accepted' || ($booking->hasContract() && $booking->status === 'completed')): ?>
            <div class="ph-card p-4">
                <h5 class="fw-semibold mb-1">Contract</h5>
                <p class="text-muted small mb-3">
                    <?php if($booking->hasContract()): ?>
                        Review the contract file from the organizer, then confirm if you agree with the terms.
                    <?php else: ?>
                        The organizer has not uploaded a contract yet. You will be notified when one is ready.
                    <?php endif; ?>
                </p>

                <?php if($booking->hasContract()): ?>
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge <?php echo e($booking->contractStatusBadgeClass()); ?>"><?php echo e($booking->contractStatusLabel(true)); ?></span>
                        <?php if($booking->performer_confirmed_contract): ?>
                            <small class="text-muted">Confirmed on <?php echo e($booking->contract_confirmed_at->format('M d, Y g:i A')); ?></small>
                        <?php endif; ?>
                    </div>

                    <a href="<?php echo e($booking->contractUrl()); ?>" target="_blank" class="btn ph-btn-outline btn-sm mb-3">
                        <i class="fas fa-file me-1"></i> Review Contract File
                    </a>

                    <?php if($booking->hasSignedContract()): ?>
                        <div class="mb-3">
                            <a href="<?php echo e($booking->signedContractUrl()); ?>" target="_blank" class="btn ph-btn-outline btn-sm">
                                <i class="fas fa-file-signature me-1"></i> View Your Signed Contract
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if(! $booking->performer_confirmed_contract): ?>
                        <form method="POST" action="<?php echo e(route('performer.bookings.signed-contract', $booking)); ?>" enctype="multipart/form-data" class="border-top pt-3 mb-3" style="border-color: var(--ph-border) !important;">
                            <?php echo csrf_field(); ?>
                            <?php if(! $booking->hasSignedContract()): ?>
                                <p class="small text-muted mb-2">Upload your signed contract before confirming.</p>
                            <?php else: ?>
                                <p class="small text-muted mb-2">Need to replace your signed copy? Upload a new one below.</p>
                            <?php endif; ?>
                            <input type="file" name="signed_contract" class="form-control ph-input mb-2" accept=".pdf,.jpg,.jpeg,.png" <?php echo e($booking->hasSignedContract() ? '' : 'required'); ?>>
                            <small class="text-muted d-block mb-2">Accepted files: PDF, JPG, JPEG, or PNG. Maximum 10 MB.</small>
                            <button type="submit" class="btn ph-btn-outline btn-sm">
                                <?php echo e($booking->hasSignedContract() ? 'Replace Signed Contract' : 'Upload Signed Contract'); ?>

                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if($booking->canConfirmContract()): ?>
                        <form method="POST" action="<?php echo e(route('performer.bookings.confirm-contract', $booking)); ?>" class="border-top pt-3" style="border-color: var(--ph-border) !important;">
                            <?php echo csrf_field(); ?>
                            <p class="small text-muted mb-2">By confirming, you agree to the contract terms for this booking.</p>
                            <button type="submit" class="btn ph-btn-primary btn-sm">
                                <i class="fas fa-check-circle me-1"></i> Confirm Contract
                            </button>
                        </form>
                    <?php elseif($booking->performer_confirmed_contract): ?>
                        <p class="text-success small mb-0"><i class="fas fa-check-circle me-1"></i> You have confirmed this contract.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\performer\bookings\show.blade.php ENDPATH**/ ?>