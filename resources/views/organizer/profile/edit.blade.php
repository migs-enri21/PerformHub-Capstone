@extends('layouts.app')

@section('title', 'Organizer Profile')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Organizer Profile</h2>
<form method="POST" action="{{ route('organizer.profile.update') }}" enctype="multipart/form-data" id="profileForm">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="ph-card p-4 profile-photo-card">
                @include('partials.profile-photo-upload', [
                    'currentUrl' => $profile->profile_photo ? asset('storage/'.$profile->profile_photo) : null,
                    'fallbackName' => $profile->organization_name,
                ])
            </div>
        </div>
        <div class="col-lg-8">
            <div class="ph-card p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">First Name</label>
                        <input type="text" name="first_name" class="form-control ph-input" value="{{ old('first_name', auth()->user()->first_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Last Name</label>
                        <input type="text" name="last_name" class="form-control ph-input" value="{{ old('last_name', auth()->user()->last_name) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Organization Name</label>
                        <input type="text" name="organization_name" class="form-control ph-input" value="{{ old('organization_name', $profile->organization_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Phone</label>
                        <input type="text" name="phone" class="form-control ph-input" value="{{ old('phone', $profile->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Website</label>
                        <input type="url" name="website" class="form-control ph-input" value="{{ old('website', $profile->website) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Location</label>
                        @include('partials.location-select', [
                            'region' => $profile->region,
                            'city' => $profile->city,
                            'barangay' => $profile->barangay,
                        ])
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Bio</label>
                        <textarea name="bio" class="form-control ph-input" rows="4">{{ old('bio', $profile->bio) }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn ph-btn-primary mt-4">Save Profile</button>
            </div>
        </div>
    </div>
</form>
@endsection
