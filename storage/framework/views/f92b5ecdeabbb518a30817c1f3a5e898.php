<div class="sidebar-profile-block text-center mb-4">
    <img
        src="<?php echo e(auth()->user()->avatarUrl(96)); ?>"
        alt="<?php echo e(auth()->user()->name); ?>"
        class="sidebar-profile-avatar rounded-circle mb-2"
        width="72"
        height="72"
    >
    <div class="sidebar-profile-name"><?php echo e(auth()->user()->name); ?></div>
    <div class="sidebar-profile-role text-muted small">Admin</div>
</div>

<nav class="nav flex-column">
    <a class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>"><i class="fas fa-home me-2"></i> Dashboard</a>
    <a class="nav-link <?php echo e(request()->routeIs('admin.users.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.users.index')); ?>"><i class="fas fa-users me-2"></i> Users</a>
    <a class="nav-link <?php echo e(request()->routeIs('admin.categories.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.categories.index')); ?>"><i class="fas fa-tags me-2"></i> Categories & Event Types</a>
    <a class="nav-link <?php echo e(request()->routeIs('admin.events.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.events.index')); ?>"><i class="fas fa-calendar-alt me-2"></i> User History</a>
    <a class="nav-link <?php echo e(request()->routeIs('admin.monitoring.bookings') ? 'active' : ''); ?>" href="<?php echo e(route('admin.monitoring.bookings')); ?>"><i class="fas fa-ticket me-2"></i> Bookings</a>
</nav>

<form action="<?php echo e(route('logout')); ?>" method="POST" class="mt-auto">
    <?php echo csrf_field(); ?>
    <button type="submit" class="nav-link sidebar-logout-btn w-100 text-start border-0 bg-transparent">
        <i class="fas fa-sign-out-alt me-2"></i> Logout
    </button>
</form>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views/admin/partials/sidebar.blade.php ENDPATH**/ ?>