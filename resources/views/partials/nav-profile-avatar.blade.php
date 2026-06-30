@php $user = auth()->user(); @endphp
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
            src="{{ $user->avatarUrl(80) }}"
            alt="{{ $user->name }}"
            class="nav-profile-avatar"
            width="36"
            height="36"
        >
    </a>
    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end nav-profile-menu">
        <li class="dropdown-header text-truncate">{{ $user->name }}</li>
        @if($user->profileRoute())
            <li>
                <a class="dropdown-item" href="{{ $user->profileRoute() }}">
                    <i class="fas fa-user me-2"></i> Profile
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
        @endif
        <li>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item">Logout</button>
            </form>
        </li>
    </ul>
</li>
