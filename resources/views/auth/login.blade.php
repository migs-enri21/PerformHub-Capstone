@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="auth-split">
    <div class="auth-hero d-none d-lg-block">
        <div class="p-4">
            <a href="{{ route('home') }}" class="text-white text-decoration-none fw-bold fs-5">
                <i class="fas fa-music me-2"></i>PerformHub
            </a>
        </div>
        <div class="auth-hero-content">
            <h1 class="display-5 fw-bold">Welcome back to the stage.</h1>
            <p class="text-white-50 fs-5">Thousands of performers and organizers are waiting for you.</p>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="w-100" style="max-width: 440px; margin: 0 auto;">
            <a href="{{ route('home') }}" class="text-muted small mb-4 d-inline-block">
                <i class="fas fa-chevron-left me-1"></i> Back to Home
            </a>

            <h2 class="fw-bold mb-1">Sign in to your account</h2>
            <p class="text-muted mb-4">Enter your credentials to continue</p>

            @if($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="{{ old('role', $role) }}">

                <div class="mb-3">
                    <label class="form-label text-muted small">Email Address</label>
                    <input type="email" name="email" class="form-control ph-input" placeholder="you@example.com" value="{{ old('email') }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small">Password</label>
                    <input type="password" name="password" class="form-control ph-input" placeholder="••••••••" required>
                </div>

                <label class="form-label text-muted small mb-2">Sign in as</label>
                <div class="row g-2 mb-4">
                    @foreach(['organizer' => ['icon' => 'fa-building', 'label' => 'Organizer'], 'performer' => ['icon' => 'fa-microphone', 'label' => 'Performer'], 'admin' => ['icon' => 'fa-shield-halved', 'label' => 'Admin']] as $key => $item)
                        <div class="col-4">
                            <div class="role-card {{ old('role', $role) === $key ? 'active' : '' }}" data-role="{{ $key }}">
                                <i class="fas {{ $item['icon'] }}"></i>
                                <span class="small fw-semibold d-block">{{ $item['label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn ph-btn-primary w-100 mb-3">
                    Sign In <i class="fas fa-arrow-right ms-2"></i>
                </button>

                <p class="text-center text-muted small mb-0">
                    Don't have an account? <a href="{{ route('register') }}">Create one</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        document.getElementById('roleInput').value = card.dataset.role;
    });
});
</script>
@endpush
