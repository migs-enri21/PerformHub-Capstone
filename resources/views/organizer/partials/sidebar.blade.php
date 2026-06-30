<nav class="nav flex-column">
    <a class="nav-link {{ request()->routeIs('organizer.dashboard') ? 'active' : '' }}" href="{{ route('organizer.dashboard') }}"><i class="fas fa-home me-2"></i> Dashboard</a>
    <a class="nav-link {{ request()->routeIs('organizer.profile.*') ? 'active' : '' }}" href="{{ route('organizer.profile.edit') }}"><i class="fas fa-user me-2"></i> Profile</a>
    <a class="nav-link {{ request()->routeIs('organizer.performers.*') ? 'active' : '' }}" href="{{ route('organizer.performers.index') }}"><i class="fas fa-search me-2"></i> Find Performers</a>
    <a class="nav-link {{ request()->routeIs('organizer.bookings.*') ? 'active' : '' }}" href="{{ route('organizer.bookings.index') }}"><i class="fas fa-ticket me-2"></i> Bookings</a>
    <a class="nav-link" href="{{ route('messages.index') }}"><i class="fas fa-envelope me-2"></i> Messages</a>
    @if(auth()->user()->hasLimitedAccess())
        <a class="nav-link text-warning" href="{{ auth()->user()->onboardingRoute() }}"><i class="fas fa-arrow-right me-2"></i> Complete Sign-up</a>
    @endif
</nav>
