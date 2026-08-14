<?php $user = auth()->user(); ?>
<li class="nav-item dropdown">
    <a
        class="nav-link nav-profile-toggle dropdown-toggle"
        href="#"
        role="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        title="Account"
    >
        <img
            src="<?php echo e($user->avatarUrl(80)); ?>"
            alt="<?php echo e($user->name); ?>"
            class="nav-profile-avatar"
            width="36"
            height="36"
        >
    </a>
    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end nav-profile-menu">
        <li class="dropdown-header text-truncate"><?php echo e($user->name); ?></li>
        <?php if($user->profileRoute()): ?>
            <li>
                <a class="dropdown-item" href="<?php echo e($user->profileRoute()); ?>">
                    <i class="fas fa-user me-2"></i> Profile
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
        <?php endif; ?>
        <li>
            <form action="<?php echo e(route('logout')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" class="dropdown-item">Logout</button>
            </form>
        </li>
    </ul>
</li>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\partials\nav-profile-avatar.blade.php ENDPATH**/ ?>