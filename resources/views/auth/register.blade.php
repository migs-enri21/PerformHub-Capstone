@extends('layouts.guest')

@section('title', 'Register')

@section('content')
<div class="auth-split auth-register">
    <div class="auth-hero d-none d-lg-block">
        <div class="p-4">
            <a href="{{ route('home') }}" class="text-white text-decoration-none fw-bold fs-5 d-flex align-items-center">
                <img src="{{ asset('images/logo.png') }}" alt="PerformHub" height="32" width="32" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub
            </a>
        </div>
        <div class="auth-hero-content">
            <h1 class="display-5 fw-bold">Join the stage! </h1>
            <p class="text-white-50 fs-5">Create your account and start connecting today.</p>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="w-100" style="max-width: 440px; margin: 0 auto;">
            <a href="{{ route('home') }}" class="text-muted small mb-2 d-inline-block">
                <i class="fas fa-chevron-left me-1"></i> Back to Home
            </a>

            <h2 class="fw-bold mb-1">Create your account</h2>
            <p class="text-muted mb-3">Choose your role and get started</p>

            @if($errors->any())
                <div class="alert alert-danger py-2 mb-3">
                    <ul class="mb-0 small ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $showRegistration = ($errors->any() && !$errors->has('terms_accepted')) || old('terms_accepted');
            @endphp

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="role" id="roleInput" value="{{ old('role', $role) }}">

                <section id="termsStep" class="border rounded p-3 mb-3" style="{{ $showRegistration ? 'display:none;' : '' }}">
                    <p class="text-muted small mb-3">Step 1 of 2: Review and accept the Terms & Agreement before creating your account.</p>
                    <div class="border rounded p-3 mb-3" role="region" aria-label="Terms and Agreement" tabindex="0" style="max-height: 280px; overflow-y: auto; font-size: .875rem;">
                        @include('partials.terms-content')
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input @error('terms_accepted') is-invalid @enderror" type="checkbox" name="terms_accepted" id="termsAccepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }}>
                        <label class="form-check-label" for="termsAccepted">I have read and agree to the Terms & Conditions and Disclaimer.</label>
                    </div>
                    @error('terms_accepted')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                    <button type="button" id="continueRegistration" class="btn ph-btn-primary w-100" disabled>
                        Continue to Account <i class="fas fa-arrow-right ms-2" aria-hidden="true"></i>
                    </button>
                </section>

                <div id="registrationFields" style="{{ $showRegistration ? '' : 'display:none;' }}">
                    <p class="text-muted small mb-3">Step 2 of 2: Enter your account details</p>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">First Name</label>
                            <input type="text" name="first_name" class="form-control ph-input" value="{{ old('first_name') }}" pattern="[A-Za-z\s'-]+" title="Use letters only; spaces, hyphens, and apostrophes are allowed." oninput="this.value = this.value.replace(/[^A-Za-z\s'-]/g, '')" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Last Name</label>
                            <input type="text" name="last_name" class="form-control ph-input" value="{{ old('last_name') }}" pattern="[A-Za-z\s'-]+" title="Use letters only; spaces, hyphens, and apostrophes are allowed." oninput="this.value = this.value.replace(/[^A-Za-z\s'-]/g, '')" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Email Address</label>
                            <input type="email" name="email" class="form-control ph-input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Phone Number</label>
                            <input type="tel" name="phone" class="form-control ph-input @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="0917 123 4567" inputmode="tel" autocomplete="tel" pattern="^(\+63|0|63)?[\s\-]*9\d{2}[\s\-]*\d{3}[\s\-]*\d{4}$" title="Enter a valid Philippine mobile number (e.g. 09XX XXX XXXX or +63 9XX XXX XXXX)." required>
                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Password</label>
                            <div class="input-group password-input-group">
                                <input type="password" name="password" id="registerPassword" class="form-control ph-input @error('password') is-invalid @enderror" required autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary" data-password-toggle="registerPassword" aria-label="Show password" aria-pressed="false" title="Show password">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Confirm Password</label>
                            <div class="input-group password-input-group">
                                <input type="password" name="password_confirmation" id="registerPasswordConfirmation" class="form-control ph-input @error('password') is-invalid @enderror" required autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary" data-password-toggle="registerPasswordConfirmation" aria-label="Show password" aria-pressed="false" title="Show password">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted small mb-1">Location</label>
                        @include('partials.location-select', [
                            'region' => old('region'),
                            'city' => old('city'),
                            'barangay' => old('barangay'),
                            'required' => true,
                        ])
                    </div>

                    <label class="form-label text-muted small mb-1">Register as</label>
                    <div class="row g-2 mb-2">
                        @foreach(['performer' => ['icon' => 'fa-microphone', 'label' => 'Performer'], 'organizer' => ['icon' => 'fa-building', 'label' => 'Organizer']] as $key => $item)
                            <div class="col-6">
                                <div class="role-card {{ old('role', $role) === $key ? 'active' : '' }}" data-role="{{ $key }}">
                                    <i class="fas {{ $item['icon'] }}"></i>
                                    <span class="small fw-semibold d-block">{{ $item['label'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="text-muted small mb-1">Identity Verification</p>
                    @include('onboarding.partials.upload-field', [
                        'name' => 'government_id',
                        'title' => 'Government-Issued ID',
                        'required' => true,
                        'multiple' => true,
                        'desc' => 'Valid Philippine ID for identity verification.',
                        'formats' => '.jpg .png .pdf — up to 5 files, max 5 MB each',
                        'icon' => 'fa-id-card',
                    ])

                    <button type="submit" id="registerSubmitButton" class="btn ph-btn-primary w-100 mb-2">
                        Continue to Verification <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                    <p class="text-center text-muted small mb-2">Your account is created after you submit verification.</p>

                    <p class="text-center text-muted small mb-0">
                        Already have an account? <a href="{{ route('login') }}">Sign in</a>
                    </p>
                </div>
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

const termsCheckbox = document.getElementById('termsAccepted');
const continueButton = document.getElementById('continueRegistration');
const termsStep = document.getElementById('termsStep');
const registrationFields = document.getElementById('registrationFields');

if (termsCheckbox && continueButton) {
    const updateContinueState = () => {
        continueButton.disabled = !termsCheckbox.checked;
    };

    termsCheckbox.addEventListener('change', updateContinueState);
    updateContinueState();

    continueButton.addEventListener('click', () => {
        if (termsCheckbox.checked) {
            termsStep.style.display = 'none';
            registrationFields.style.display = 'block';
        }
    });
}
</script>
@endpush
