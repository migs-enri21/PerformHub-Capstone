@extends('onboarding.layout', ['title' => 'Your Information', 'current' => 1])

@section('onboarding-content')
<h2 class="fw-bold text-center mb-1">Your information</h2>
<p class="text-muted text-center mb-4">
    Set up your {{ $user->isPerformer() ? 'performer' : 'organizer' }} profile
</p>

<form method="POST" action="{{ route('onboarding.profile.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label text-muted small">First Name</label>
            <input type="text" class="form-control ph-input" value="{{ $user->first_name }}" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label text-muted small">Last Name</label>
            <input type="text" class="form-control ph-input" value="{{ $user->last_name }}" readonly>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label text-muted small">Email Address</label>
        <input type="email" class="form-control ph-input" value="{{ $user->email }}" readonly>
    </div>

    <div class="mb-3">
        <label class="form-label text-muted small">Phone Number</label>
        <input type="text" class="form-control ph-input" value="{{ $user->phone }}" readonly>
    </div>

    @if($user->isOrganizer())
        <div class="mb-3">
            <label for="organization_name" class="form-label text-muted small">Organization Name</label>
            <input type="text" id="organization_name" name="organization_name" class="form-control ph-input"
                value="{{ old('organization_name', $user->organizerProfile?->organization_name) }}" maxlength="255" required>
            @error('organization_name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    @endif

    <div class="mb-4">
        <label class="form-label text-muted small mb-2">Location</label>
        @php
            $profile = $user->isPerformer() ? $user->performerProfile : $user->organizerProfile;
        @endphp
        @include('partials.location-select', [
            'latitude' => $profile?->latitude,
            'longitude' => $profile?->longitude,
            'location' => $profile?->location,
            'required' => true,
        ])
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn ph-btn-primary flex-grow-1">
            Submit for Verification <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
</form>
@endsection
