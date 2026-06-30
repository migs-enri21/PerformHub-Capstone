@extends('onboarding.layout', ['title' => 'Your Information', 'current' => 2])

@section('onboarding-content')
<h2 class="fw-bold text-center mb-1">Your information</h2>
<p class="text-muted text-center mb-4">
    Set up your {{ $user->isPerformer() ? 'performer' : 'organizer' }} profile
</p>

<form method="POST" action="{{ route('onboarding.profile.store') }}">
    @csrf

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label text-muted small">First Name</label>
            <input type="text" name="first_name" class="form-control ph-input" value="{{ old('first_name', $user->first_name) }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label text-muted small">Last Name</label>
            <input type="text" name="last_name" class="form-control ph-input" value="{{ old('last_name', $user->last_name) }}" required>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label text-muted small">Email Address</label>
        <input type="email" class="form-control ph-input" value="{{ $user->email }}" disabled>
    </div>

    <div class="mb-3">
        <label class="form-label text-muted small">Phone Number</label>
        <input type="text" name="phone" class="form-control ph-input" value="{{ old('phone', $user->phone) }}" placeholder="+63 9XX XXX XXXX" required>
    </div>

    <div class="mb-4">
        <label class="form-label text-muted small mb-2">Location</label>
        @php
            $profile = $user->isPerformer() ? $user->performerProfile : $user->organizerProfile;
        @endphp
        @include('partials.location-select', [
            'region' => $profile?->region,
            'city' => $profile?->city,
            'barangay' => $profile?->barangay,
            'required' => true,
        ])
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('onboarding.role') }}" class="btn ph-btn-outline">Back</a>
        <button type="submit" class="btn ph-btn-primary flex-grow-1">
            Continue <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
</form>
@endsection
