<nav class="nav flex-column">
    <a class="nav-link {{ request()->routeIs('performer.dashboard') ? 'active' : '' }}" href="{{ route('performer.dashboard') }}"><i class="fas fa-home me-2"></i> Dashboard</a>
    @if(auth()->user()->hasLimitedAccess())
        <span class="nav-link text-muted opacity-50" title="Complete sign-up to unlock"><i class="fas fa-lock me-2"></i> Portfolio</span>
        <a class="nav-link {{ request()->routeIs('performer.profile.*') ? 'active' : '' }}" href="{{ route('performer.profile.show') }}#availability"><i class="fas fa-calendar me-2"></i> Availability</a>
        <a class="nav-link {{ request()->routeIs('performer.bookings.*') ? 'active' : '' }}" href="{{ route('performer.bookings.index') }}"><i class="fas fa-ticket me-2"></i> Bookings</a>
    @else
        <a class="nav-link {{ request()->routeIs('performer.portfolio.*') ? 'active' : '' }}" href="{{ route('performer.portfolio.index') }}"><i class="fas fa-images me-2"></i> Portfolio</a>
        <a class="nav-link {{ request()->routeIs('performer.profile.*') ? 'active' : '' }}" href="{{ route('performer.profile.show') }}#availability"><i class="fas fa-calendar me-2"></i> Availability</a>
        <a class="nav-link {{ request()->routeIs('performer.bookings.*') ? 'active' : '' }}" href="{{ route('performer.bookings.index') }}"><i class="fas fa-ticket me-2"></i> Bookings</a>
    @endif
    @if(auth()->user()->hasLimitedAccess())
        <a class="nav-link text-warning" href="{{ auth()->user()->onboardingRoute() }}"><i class="fas fa-arrow-right me-2"></i> Complete Sign-up</a>
    @endif
</nav>
