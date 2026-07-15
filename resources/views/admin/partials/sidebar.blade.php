<nav class="nav flex-column">
    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="fas fa-home me-2"></i> Dashboard</a>
    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="fas fa-users me-2"></i> Users</a>
    <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><i class="fas fa-tags me-2"></i> Categories</a>
    <a class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}" href="{{ route('admin.events.index') }}"><i class="fas fa-calendar-alt me-2"></i> User History</a>
    <a class="nav-link {{ request()->routeIs('admin.monitoring.bookings') ? 'active' : '' }}" href="{{ route('admin.monitoring.bookings') }}"><i class="fas fa-ticket me-2"></i> Bookings</a>
</nav>
