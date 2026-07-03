@extends('layouts.app')

@section('title', 'My Profile')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success border-0 mb-4" style="background: rgba(34, 197, 94, 0.12); color: #86efac;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger border-0 mb-4" style="background: rgba(239, 68, 68, 0.12); color: #fca5a5;">
        {{ session('error') }}
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0">My Profile</h2>
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
    <div class="col-12" id="availability">
        <div class="ph-card p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-semibold mb-0">Availability Calendar</h5>
                <div class="d-flex flex-wrap gap-2">
                    @if(! auth()->user()->hasLimitedAccess())
                        @if($profile->google_calendar_connected)
                            <form method="POST" action="{{ route('performer.google-calendar.sync') }}">
                                @csrf
                                <button type="submit" class="btn btn-sm ph-btn-outline">
                                    <i class="fab fa-google me-1"></i> Sync Google Calendar
                                </button>
                            </form>
                            <form method="POST" action="{{ route('performer.google-calendar.disconnect') }}">
                                @csrf
                                <button type="submit" class="btn btn-sm ph-btn-outline">
                                    Disconnect Google
                                </button>
                            </form>
                        @else
                            <a href="{{ route('performer.google-calendar.connect') }}" class="btn btn-sm ph-btn-outline">
                                <i class="fab fa-google me-1"></i> Connect Google Calendar
                            </a>
                        @endif
                    @elseif(auth()->user()->hasLimitedAccess())
                        <a href="{{ auth()->user()->onboardingRoute() }}" class="btn btn-sm ph-btn-primary">
                            <i class="fas fa-lock me-1"></i> Complete sign-up to manage
                        </a>
                    @endif
                </div>
            </div>
            @if($profile->google_calendar_connected)
                <p class="text-muted small mb-3">
                    Google Calendar connected
                    @if($profile->google_calendar_synced_at)
                        · Last synced {{ $profile->google_calendar_synced_at->diffForHumans() }}
                    @endif
                </p>
            @endif
            @include('partials.availability-calendar', [
                'schedules' => $calendar['schedules'],
                'bookingCalendar' => $calendar['bookingCalendar'],
                'googleBusy' => $calendar['googleBusy'],
                'editable' => ! auth()->user()->hasLimitedAccess(),
                'storeUrl' => route('performer.availability.store'),
            ])
        </div>
    </div>
    <div class="col-12">
        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-3">Portfolio Posts</h5>
            @include('partials.portfolio-feed', [
                'posts' => $portfolioGroups->values(),
                'ownProfileId' => $profile->id,
                'emptyMessage' => 'No posts yet. Upload photos or videos from Manage Portfolio to share your work.',
            ])
        </div>
    </div>
</div>
@endsection
