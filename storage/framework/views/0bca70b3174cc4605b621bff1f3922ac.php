<?php $__env->startSection('onboarding-content'); ?>
<h2 class="fw-bold text-center mb-1">Join PerformHub</h2>
<p class="text-muted text-center mb-4">Tell us how you'll use the platform</p>

<form method="POST" action="<?php echo e(route('onboarding.role.store')); ?>">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="role" id="roleInput" value="<?php echo e(old('role', $user->role)); ?>">

    <div class="row g-3 mb-4">
        <?php $__currentLoopData = [
            'performer' => ['icon' => 'fa-microphone', 'title' => 'I am a Performer', 'desc' => 'Showcase your talent, manage bookings, and grow your audience.'],
            'organizer' => ['icon' => 'fa-building', 'title' => 'I am an Organizer', 'desc' => 'Find and book performers for your events and venues.'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-12">
                <div class="onboarding-role-card <?php echo e(old('role', $user->role) === $key ? 'active' : ''); ?>" data-role="<?php echo e($key); ?>">
                    <div class="d-flex align-items-start gap-3">
                        <div class="onboarding-role-icon"><i class="fas <?php echo e($item['icon']); ?>"></i></div>
                        <div>
                            <h6 class="fw-semibold mb-1"><?php echo e($item['title']); ?></h6>
                            <p class="text-muted small mb-0"><?php echo e($item['desc']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <button type="submit" class="btn ph-btn-primary w-100">
        Continue <i class="fas fa-arrow-right ms-2"></i>
    </button>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.onboarding-role-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.onboarding-role-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        document.getElementById('roleInput').value = card.dataset.role;
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('onboarding.layout', ['title' => 'Choose Your Role', 'current' => 1], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\onboarding\role.blade.php ENDPATH**/ ?>