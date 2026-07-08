<nav class="nav flex-column">
    <a class="nav-link {{ request()->routeIs('organizer.dashboard') ? 'active' : '' }}" href="{{ route('organizer.dashboard') }}"><i class="fas fa-home me-2"></i> Dashboard</a>
    <a class="nav-link {{ request()->routeIs('organizer.performers.*') ? 'active' : '' }}" href="{{ route('organizer.performers.index') }}"><i class="fas fa-search me-2"></i> Find Performers</a>
    <a class="nav-link {{ request()->routeIs('organizer.bookings.*') ? 'active' : '' }}" href="{{ route('organizer.bookings.index') }}"><i class="fas fa-ticket me-2"></i> Bookings</a>
    @if(auth()->user()->hasLimitedAccess())
        <a class="nav-link text-warning" href="{{ auth()->user()->onboardingRoute() }}"><i class="fas fa-arrow-right me-2"></i> Complete Sign-up</a>
    @endif
</nav>
