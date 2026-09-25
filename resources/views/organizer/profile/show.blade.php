@extends('layouts.app')

@section('title', 'My Profile')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-end mb-2">
    <a href="{{ route('organizer.profile.edit') }}" class="btn ph-btn-outline btn-sm">
        <i class="fas fa-pen me-1"></i> Edit Profile
    </a>
</div>

@include('partials.organizer-profile-header', [
    'organizer' => $profile,
    'editable' => true,
])

<div class="row g-4">
    <div class="col-md-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-semibold mb-3">Contact Info</h5>
            <p class="text-muted mb-1"><i class="fas fa-phone me-2"></i>
                @if($profile->phone)
                    {{ $profile->phone }}
                @else
                    No phone number set.
                @endif
            </p>
            <p class="mb-0">
                <i class="fas fa-globe me-2 text-muted"></i>
                @if($profile->website)
                    <a href="{{ $profile->website }}" target="_blank" rel="noopener">{{ $profile->website }}</a>
                @else
                    <span class="text-muted">No website set.</span>
                @endif
            </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="ph-card p-4 h-100">
            <h5 class="fw-semibold mb-3">Location</h5>
            <p class="text-muted mb-0">
                @if($profile->fullLocation())
                    {{ $profile->fullLocation() }}
                @else
                    No location set yet.
                @endif
            </p>
        </div>
    </div>
</div>

<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-semibold mb-0">My Events</h5>
        <a href="{{ route('organizer.events.index') }}" class="btn ph-btn-outline btn-sm">View All Events</a>
    </div>

    @if($events->isNotEmpty())
        <div class="row justify-content-center">
            <div class="col-lg-9">
            @foreach($events as $event)
                @include('partials.event-activity-post', ['event' => $event])
            @endforeach
            </div>
        </div>
    @else
        <div class="ph-card p-4 text-muted">You have not created any events yet.</div>
    @endif
</div>
@endsection
