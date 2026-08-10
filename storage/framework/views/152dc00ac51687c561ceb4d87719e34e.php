<?php $__env->startSection('onboarding-content'); ?>
<div class="d-flex align-items-center justify-content-center gap-2 mb-1">
    <h2 class="fw-bold text-center mb-0">
        <?php echo e($user->isOrganizer() ? 'Verify Your Organization' : 'Verify Your Identity'); ?>

    </h2>
    <?php if($user->isOrganizer()): ?>
        <span class="badge rounded-pill" style="background:#2563eb;">Organizer</span>
    <?php else: ?>
        <span class="badge rounded-pill" style="background:#6346ff;">Performer</span>
    <?php endif; ?>
</div>
<p class="text-muted text-center mb-4">
    <?php if($user->isOrganizer()): ?>
        Performers need to trust you before accepting bookings. Verify your legitimacy with official documents.
    <?php else: ?>
        Organizers look for verified performers. Upload your ID to build trust on the platform.
    <?php endif; ?>
</p>

<form method="POST" action="<?php echo e(route('onboarding.verification.store')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <?php if($user->isOrganizer()): ?>
        <label class="form-label text-muted small mb-2">Organization Type <span class="text-danger">*</span></label>
        <div class="row g-2 mb-4">
            <?php $__currentLoopData = [
                'company' => ['icon' => 'fa-building', 'label' => 'Company / Corp.'],
                'individual' => ['icon' => 'fa-user', 'label' => 'Individual / Solo'],
                'nonprofit' => ['icon' => 'fa-globe', 'label' => 'Non-Profit / NGO'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-4">
                    <label class="org-type-card <?php echo e(old('organization_type', $user->organizerProfile?->organization_type) === $type ? 'active' : ''); ?>">
                        <input type="radio" name="organization_type" value="<?php echo e($type); ?>" class="d-none"
                            <?php echo e(old('organization_type', $user->organizerProfile?->organization_type) === $type ? 'checked' : ''); ?> required>
                        <i class="fas <?php echo e($item['icon']); ?> d-block mb-2"></i>
                        <span class="small"><?php echo e($item['label']); ?></span>
                    </label>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <p class="text-muted small mb-3">Upload Documents <span class="text-muted">(Required items marked)</span></p>

        <?php echo $__env->make('onboarding.partials.upload-field', [
            'name' => 'government_id',
            'title' => 'Government-Issued ID',
            'required' => true,
            'desc' => 'Valid Philippine ID of the authorized representative.',
            'formats' => '.jpg .png .pdf — max 5 MB',
            'icon' => 'fa-id-card',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('onboarding.partials.upload-field', [
            'name' => 'business_permit',
            'title' => 'Business / Organization Permit',
            'required' => true,
            'desc' => 'DTI Certificate, SEC Registration, Mayor\'s Permit, or equivalent.',
            'formats' => '.jpg .png .pdf — max 10 MB',
            'icon' => 'fa-file-alt',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('onboarding.partials.upload-field', [
            'name' => 'proof_of_events',
            'title' => 'Proof of Previous Events',
            'required' => false,
            'desc' => 'Event photos, contracts, or letters confirming past event experience.',
            'formats' => '.jpg .png .pdf .zip — max 50 MB',
            'icon' => 'fa-camera',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('onboarding.partials.upload-field', [
            'name' => 'bir_certificate',
            'title' => 'BIR Certificate of Registration',
            'required' => false,
            'desc' => 'If your organization issues official receipts.',
            'formats' => '.jpg .png .pdf — max 5 MB',
            'icon' => 'fa-certificate',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <?php echo $__env->make('onboarding.partials.upload-field', [
            'name' => 'government_id',
            'title' => 'Government-Issued ID',
            'required' => true,
            'desc' => 'Valid Philippine ID for identity verification.',
            'formats' => '.jpg .png .pdf — max 5 MB',
            'icon' => 'fa-id-card',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('onboarding.partials.upload-field', [
            'name' => 'performance_sample',
            'title' => 'Performance Sample',
            'required' => false,
            'desc' => 'Photo, video, or portfolio sample showcasing your talent.',
            'formats' => '.jpg .png .pdf .mp4 — max 500 MB',
            'icon' => 'fa-video',
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <div class="d-flex gap-2">
        <a href="<?php echo e(route('onboarding.profile')); ?>" class="btn ph-btn-outline">Back</a>
        <button type="submit" class="btn ph-btn-primary flex-grow-1">
            Submit for Verification <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.org-type-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.org-type-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        card.querySelector('input[type=radio]').checked = true;
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('onboarding.layout', ['title' => 'Verification', 'current' => 3], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/onboarding/verification.blade.php ENDPATH**/ ?>