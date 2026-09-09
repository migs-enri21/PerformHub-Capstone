@extends('layouts.app')

@section('title', 'My Profile')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-end mb-2">
    <a href="{{ route('performer.profile.edit') }}" class="btn ph-btn-outline btn-sm">
        <i class="fas fa-pen me-1"></i> Edit Profile
    </a>
</div>

@include('partials.performer-profile-header', [
    'performer' => $profile,
    'editable' => true,
])

<div class="row g-4">
    <div class="col-md-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-semibold mb-3">Booking Rate</h5>
            @if($profile->rate)
                <p class="fw-semibold mb-0 fs-5">₱{{ number_format($profile->rate, 2) }} <span class="text-muted small fw-normal">/ event</span></p>
            @else
                <p class="text-muted mb-0">No rate set yet. <a href="{{ route('performer.profile.edit') }}">Add your rate</a>.</p>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-semibold mb-3">Location</h5>
            <p class="text-muted mb-0">{{ $profile->fullLocation() ?: 'No location set yet.' }}</p>
        </div>
    </div>
    @if($profile->socialLinks())
        <div class="col-12">
            @include('partials.social-media-section', ['performer' => $profile, 'editable' => true])
        </div>
    @endif
</div>
@endsection
