<a href="{{ route('organizer.profile.edit') }}" class="sidebar-profile-block d-block text-center text-decoration-none mb-4">
    <img
        src="{{ auth()->user()->avatarUrl(96) }}"
        alt="{{ auth()->user()->name }}"
        class="sidebar-profile-avatar rounded-circle mb-2"
        width="72"
        height="72"
    >
    <div class="sidebar-profile-name">{{ auth()->user()->name }}</div>
    <div class="sidebar-profile-role text-muted small">
        Organizer
        @if(auth()->user()->is_verified)
            <i class="fas fa-circle-check text-success ms-1" title="Verified"></i>
        @endif
    </div>
</a>

<nav class="nav flex-column">
    <a class="nav-link {{ request()->routeIs('organizer.dashboard') ? 'active' : '' }}" href="{{ route('organizer.dashboard') }}"><i class="fas fa-home me-2"></i> Dashboard</a>
    <a class="nav-link {{ request()->routeIs('organizer.events.*') ? 'active' : '' }}" href="{{ route('organizer.events.create') }}"><i class="fas fa-plus me-2"></i> Create Event</a>
    <a class="nav-link {{ request()->routeIs('organizer.performers.*') ? 'active' : '' }}" href="{{ route('organizer.performers.index') }}"><i class="fas fa-search me-2"></i> Find Performers</a>
    <a class="nav-link {{ request()->routeIs('organizer.bookings.*') ? 'active' : '' }}" href="{{ route('organizer.bookings.index') }}"><i class="fas fa-ticket me-2"></i> Bookings</a>
    <a class="nav-link {{ request()->routeIs('organizer.history.*') ? 'active' : '' }}" href="{{ route('organizer.history.index') }}"><i class="fas fa-history me-2"></i> Event History</a>
    @if(auth()->user()->hasLimitedAccess())
        <a class="nav-link text-warning" href="{{ auth()->user()->onboardingRoute() }}"><i class="fas fa-arrow-right me-2"></i> Complete Sign-up</a>
    @endif
</nav>

<form action="{{ route('logout') }}" method="POST" class="mt-auto">
    @csrf
    <button type="submit" class="nav-link sidebar-logout-btn w-100 text-start border-0 bg-transparent">
        <i class="fas fa-sign-out-alt me-2"></i> Logout
    </button>
</form>
