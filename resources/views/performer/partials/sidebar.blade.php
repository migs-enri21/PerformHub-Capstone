<a href="{{ route('performer.profile.show') }}" class="sidebar-profile-block d-block text-center text-decoration-none mb-4">
    <img
        src="{{ auth()->user()->avatarUrl(96) }}"
        alt="{{ auth()->user()->name }}"
        class="sidebar-profile-avatar rounded-circle mb-2"
        width="72"
        height="72"
    >
    <div class="sidebar-profile-name">{{ auth()->user()->name }}</div>
    <div class="sidebar-profile-role text-muted small">
        Performer
        @if(auth()->user()->performerProfile?->is_verified_badge)
            <i class="fas fa-circle-check text-success ms-1" title="Verified"></i>
        @endif
    </div>
</a>

<nav class="nav flex-column">
    <a class="nav-link {{ request()->routeIs('performer.dashboard') ? 'active' : '' }}" href="{{ route('performer.dashboard') }}"><i class="fas fa-home me-2"></i> Dashboard</a>
    @if(auth()->user()->hasLimitedAccess())
        <span class="nav-link text-muted opacity-50" title="Complete sign-up to unlock"><i class="fas fa-lock me-2"></i> Portfolio</span>
        <a class="nav-link {{ request()->routeIs('performer.profile.*') ? 'active' : '' }}" href="{{ route('performer.profile.show') }}#availability"><i class="fas fa-calendar me-2"></i> Calendar</a>
        <a class="nav-link {{ request()->routeIs('performer.bookings.*') ? 'active' : '' }}" href="{{ route('performer.bookings.index') }}"><i class="fas fa-ticket me-2"></i> Bookings</a>
    @else
        <a class="nav-link {{ request()->routeIs('performer.portfolio.*') ? 'active' : '' }}" href="{{ route('performer.portfolio.index') }}"><i class="fas fa-images me-2"></i> Portfolio</a>
        <a class="nav-link {{ request()->routeIs('performer.profile.*') ? 'active' : '' }}" href="{{ route('performer.profile.show') }}#availability"><i class="fas fa-calendar me-2"></i> Calendar</a>
        <a class="nav-link {{ request()->routeIs('performer.bookings.*') ? 'active' : '' }}" href="{{ route('performer.bookings.index') }}"><i class="fas fa-ticket me-2"></i> Bookings</a>
    @endif
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