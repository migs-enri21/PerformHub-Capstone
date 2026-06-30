@extends('layouts.guest')

@section('title', 'Register')

@section('content')
<div class="auth-split">
    <div class="auth-hero d-none d-lg-block">
        <div class="p-4">
            <a href="{{ route('home') }}" class="text-white text-decoration-none fw-bold fs-5">
                <i class="fas fa-music me-2"></i>PerformHub
            </a>
        </div>
        <div class="auth-hero-content">
            <h1 class="display-5 fw-bold">Join the stage.</h1>
            <p class="text-white-50 fs-5">Create your account and start connecting today.</p>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="w-100" style="max-width: 440px; margin: 0 auto;">
            <a href="{{ route('home') }}" class="text-muted small mb-4 d-inline-block">
                <i class="fas fa-chevron-left me-1"></i> Back to Home
            </a>

            <h2 class="fw-bold mb-1">Create your account</h2>
            <p class="text-muted mb-4">Choose your role and get started</p>

            @if($errors->any())
                <div class="alert alert-danger py-2 mb-3">
                    <ul class="mb-0 small ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="{{ old('role', $role) }}">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">First Name</label>
                        <input type="text" name="first_name" class="form-control ph-input" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Last Name</label>
                        <input type="text" name="last_name" class="form-control ph-input" value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Username</label>
                    <input type="text" name="username" class="form-control ph-input @error('username') is-invalid @enderror" value="{{ old('username') }}" required autocomplete="username">
                    <div class="form-text text-muted">Letters and numbers only. Spaces become underscores (e.g. wency malinao → wency_malinao).</div>
                    @error('username')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Email Address</label>
                    <input type="email" name="email" class="form-control ph-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Password</label>
                    <input type="password" name="password" class="form-control ph-input @error('password') is-invalid @enderror" required autocomplete="new-password">
                    <div class="form-text text-muted">At least 8 characters.</div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control ph-input @error('password') is-invalid @enderror" required autocomplete="new-password">
                </div>

                <label class="form-label text-muted small mb-2">Register as</label>
                <div class="row g-2 mb-4">
                    @foreach(['performer' => ['icon' => 'fa-microphone', 'label' => 'Performer'], 'organizer' => ['icon' => 'fa-building', 'label' => 'Organizer']] as $key => $item)
                        <div class="col-6">
                            <div class="role-card {{ old('role', $role) === $key ? 'active' : '' }}" data-role="{{ $key }}">
                                <i class="fas {{ $item['icon'] }}"></i>
                                <span class="small fw-semibold d-block">{{ $item['label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn ph-btn-primary w-100 mb-3">
                    Create Account <i class="fas fa-arrow-right ms-2"></i>
                </button>

                <p class="text-center text-muted small mb-0">
                    Already have an account? <a href="{{ route('login') }}">Sign in</a>
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
