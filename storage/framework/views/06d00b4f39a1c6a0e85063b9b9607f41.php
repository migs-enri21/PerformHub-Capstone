

<?php $__env->startSection('title', 'User Preview'); ?>

<?php $__env->startSection('sidebar'); ?>
<?php echo $__env->make('admin.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">User Preview</h2>
    <a href="<?php echo e(route('admin.users.index')); ?>" class="btn ph-btn-outline">Back to Users</a>
</div>

<div class="ph-card p-4 mb-4">
    <div class="row g-4">
        <div class="col-md-6">
            <h5 class="fw-bold">Basic Details</h5>
            <dl class="row">
                <dt class="col-sm-4 text-muted">Name</dt>
                <dd class="col-sm-8"><?php echo e($user->fullName()); ?></dd>

                <dt class="col-sm-4 text-muted">Username</dt>
                <dd class="col-sm-8"><?php echo e($user->username); ?></dd>

                <dt class="col-sm-4 text-muted">Email</dt>
                <dd class="col-sm-8"><?php echo e($user->email); ?></dd>

                <dt class="col-sm-4 text-muted">Role</dt>
                <dd class="col-sm-8"><?php echo e(ucfirst($user->role)); ?></dd>

                <dt class="col-sm-4 text-muted">Status</dt>
                <dd class="col-sm-8"><?php echo e($user->is_active ? 'Active' : 'Suspended'); ?></dd>

                <dt class="col-sm-4 text-muted">Verified</dt>
                <dd class="col-sm-8"><?php echo e($user->is_verified ? 'Yes' : 'No'); ?></dd>

                <dt class="col-sm-4 text-muted">Onboarding</dt>
                <dd class="col-sm-8"><?php echo e($user->onboardingStepLabel()); ?></dd>
            </dl>
        </div>

        <div class="col-md-6">
            <h5 class="fw-bold">Profile Info</h5>
            <?php if($user->isPerformer() && $user->performerProfile): ?>
                <dl class="row">
                    <dt class="col-sm-4 text-muted">Stage Name</dt>
                    <dd class="col-sm-8"><?php echo e($user->performerProfile->stage_name); ?></dd>

                    <dt class="col-sm-4 text-muted">Genre</dt>
                    <dd class="col-sm-8"><?php echo e($user->performerProfile->genre ?? '—'); ?></dd>

                    <dt class="col-sm-4 text-muted">Location</dt>
                    <dd class="col-sm-8"><?php echo e($user->performerProfile->shortLocation()); ?></dd>
                </dl>
            <?php elseif($user->isOrganizer() && $user->organizerProfile): ?>
                <dl class="row">
                    <dt class="col-sm-4 text-muted">Organization</dt>
                    <dd class="col-sm-8"><?php echo e($user->organizerProfile->organization_name); ?></dd>

                    <dt class="col-sm-4 text-muted">Type</dt>
                    <dd class="col-sm-8"><?php echo e(ucfirst($user->organizerProfile->organization_type ?? 'N/A')); ?></dd>

                    <dt class="col-sm-4 text-muted">Location</dt>
                    <dd class="col-sm-8"><?php echo e($user->organizerProfile->shortLocation()); ?></dd>
                </dl>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-bold mb-3">Verification Documents</h5>
    <?php if($user->verificationDocuments->isEmpty()): ?>
        <p class="text-muted">No verification documents uploaded yet.</p>
    <?php else: ?>
        <div class="row gy-3">
            <?php $__currentLoopData = $user->verificationDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6">
                    <div class="ph-card p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 text-capitalize"><?php echo e(str_replace('_', ' ', $document->document_type)); ?></h6>
                                <p class="text-muted small mb-0"><?php echo e($document->original_name); ?></p>
                            </div>
                            <span class="badge bg-secondary"><?php echo e($document->created_at->diffForHumans()); ?></span>
                        </div>
                        <?php
                            $path = $document->file_path;
                            $bucket = $user->isPerformer() ? 'performer-files' : 'organizer-files';
                            $url = (new App\Services\SupabaseStorageService)->url($bucket, $path);
                            $extension = pathinfo($path, PATHINFO_EXTENSION);
                        ?>

                        <?php if(in_array(strtolower($extension), ['jpg','jpeg','png'])): ?>
                            <img src="<?php echo e($url); ?>" alt="<?php echo e($document->document_type); ?>" class="img-fluid rounded">
                        <?php elseif(in_array(strtolower($extension), ['mp4','mov'])): ?>
                            <video controls class="w-100 rounded">
                                <source src="<?php echo e($url); ?>" type="video/<?php echo e(strtolower($extension)); ?>">
                                Your browser does not support video playback.
                            </video>
                        <?php else: ?>
                            <a href="<?php echo e($url); ?>" target="_blank" class="btn ph-btn-outline">Open Document</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<div class="d-flex gap-2">
    <?php if(!$user->is_verified): ?>
        <form method="POST" action="<?php echo e(route('admin.users.verify', $user)); ?>">
            <?php echo csrf_field(); ?>
            <button class="btn btn-success">Verify User</button>
        </form>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('admin.users.toggle', $user)); ?>">
        <?php echo csrf_field(); ?>
        <button class="btn btn-outline-warning"><?php echo e($user->is_active ? 'Suspend' : 'Activate'); ?></button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\GitHub\PerformHub-Capstone\resources\views/admin/users/show.blade.php ENDPATH**/ ?>