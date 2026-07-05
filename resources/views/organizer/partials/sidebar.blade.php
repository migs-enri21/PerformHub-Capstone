<nav class="nav flex-column">
    <a class="nav-link {{ request()->routeIs('organizer.dashboard') ? 'active' : '' }}" href="{{ route('organizer.dashboard') }}"><i class="fas fa-home me-2"></i> Dashboard</a>
    <a class="nav-link {{ request()->routeIs('organizer.events.*') ? 'active' : '' }}" href="{{ route('organizer.events.create') }}"><i class="fas fa-plus me-2"></i> Create Event</a>
    <a class="nav-link {{ request()->routeIs('organizer.performers.*') ? 'active' : '' }}" href="{{ route('organizer.performers.index') }}"><i class="fas fa-search me-2"></i> Find Performers</a>
    <a class="nav-link {{ request()->routeIs('organizer.bookings.*') ? 'active' : '' }}" href="{{ route('organizer.bookings.index') }}"><i class="fas fa-ticket me-2"></i> Bookings</a>
    <a class="nav-link {{ request()->routeIs('organizer.interviews.*') ? 'active' : '' }}" href="{{ route('organizer.interviews.index') }}"><i class="fas fa-video me-2"></i> Interviews</a>
    <a class="nav-link" href="{{ route('messages.index') }}"><i class="fas fa-envelope me-2"></i> Messages</a>
    <a class="nav-link {{ request()->routeIs('organizer.history.*') ? 'active' : '' }}" href="{{ route('organizer.history.index') }}"><i class="fas fa-history me-2"></i> Event History
</a>
    @if(auth()->user()->hasLimitedAccess())
        <a class="nav-link text-warning" href="{{ auth()->user()->onboardingRoute() }}"><i class="fas fa-arrow-right me-2"></i> Complete Sign-up</a>
    @endif
</nav>
