@extends('layouts.app')

@section('title', 'Calendar')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<div class="ph-card p-4" id="availability">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-semibold mb-0">Availability Calendar</h5>
    <div class="d-flex flex-wrap align-items-center gap-2">
        @if(auth()->user()->canUseBookingFeatures())
            @if($profile->google_calendar_connected)
                <form method="POST" action="{{ route('performer.google-calendar.sync') }}" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm ph-btn-outline">
                        <i class="fab fa-google me-1"></i> Sync Google Calendar
                    </button>
                </form>
                <form method="POST" action="{{ route('performer.google-calendar.disconnect') }}" class="d-inline m-0">
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
        @elseif(auth()->user()->isAwaitingVerification())
            <span class="text-warning small"><i class="fas fa-lock me-1"></i> Available after admin verification</span>
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
    'editable' => auth()->user()->canUseBookingFeatures(),
    'storeUrl' => route('performer.availability.store'),
])
</div>
@endsection