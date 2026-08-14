<a href="<?php echo e(route('organizer.profile.show')); ?>" class="sidebar-profile-block d-block text-center text-decoration-none mb-4">
    <img
        src="<?php echo e(auth()->user()->avatarUrl(96)); ?>"
        alt="<?php echo e(auth()->user()->name); ?>"
        class="sidebar-profile-avatar rounded-circle mb-2"
        width="72"
        height="72">
        
    <div class="sidebar-profile-name"> Hello, <br><?php echo e(auth()->user()->name); ?>!</div>
    <div class="sidebar-profile-role text-muted small">
        Organizer
        <?php if(auth()->user()->is_verified): ?>
            <i class="fas fa-circle-check text-success ms-1" title="Verified"></i>
        <?php endif; ?>
    </div>
</a>

<nav class="nav flex-column">
    <a class="nav-link <?php echo e(request()->routeIs('organizer.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('organizer.dashboard')); ?>"><i class="fas fa-home me-2"></i> Dashboard</a>
    <a class="nav-link <?php echo e(request()->routeIs('organizer.events.*') ? 'active' : ''); ?>" href="<?php echo e(route('organizer.events.index')); ?>"><i class="fas fa-plus me-2"></i> Events</a>
    <a class="nav-link <?php echo e(request()->routeIs('organizer.bookings.*') ? 'active' : ''); ?>" href="<?php echo e(route('organizer.bookings.index')); ?>"><i class="fas fa-handshake me-2"></i> Bookings</a>
    <a class="nav-link <?php echo e(request()->routeIs('organizer.calendar.*') ? 'active' : ''); ?>" href="<?php echo e(route('organizer.calendar.index')); ?>"><i class="fas fa-calendar-alt me-2"></i> Calendar</a>
    <a class="nav-link <?php echo e(request()->routeIs('organizer.performers.*') ? 'active' : ''); ?>" href="<?php echo e(route('organizer.performers.index')); ?>"><i class="fas fa-search me-2"></i> Find Performers</a>
    <?php if(auth()->user()->hasLimitedAccess()): ?>
        <a class="nav-link text-warning" href="<?php echo e(auth()->user()->onboardingRoute()); ?>"><i class="fas fa-arrow-right me-2"></i> Complete Sign-up</a>
    <?php endif; ?>
</nav>

<form action="<?php echo e(route('logout')); ?>" method="POST" class="mt-auto">
    <?php echo csrf_field(); ?>
    <button type="submit" class="nav-link sidebar-logout-btn w-100 text-start border-0 bg-transparent">
        <i class="fas fa-sign-out-alt me-2"></i> Logout
    </button>
</form>
<?php /**PATH C:\Users\Erico\Documents\PerformHub-Capstone\resources\views\organizer\partials\sidebar.blade.php ENDPATH**/ ?>