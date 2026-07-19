@extends('layouts.app')

@section('title', 'Organizer Dashboard')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@include('partials.onboarding-banner')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold mb-1">

            Home

        </h2>

        <p class="text-muted mb-0">

            Stay updated with your events and performer activities.

        </p>

    </div>

    <a href="{{ route('organizer.events.create') }}" class="btn ph-btn-primary">

        <i class="fas fa-plus me-2"></i>

        Create Event

    </a>

</div>
<p class="text-muted mb-4">
    @if(auth()->user()->hasLimitedAccess())
        Manage your events and discover talent — complete sign-up to book performers.
    @else
        Manage your events and discover talent
    @endif
</p>

<div class="ph-card p-4 mb-4">

    <h5 class="fw-bold mb-4">

    Recent Activity

    </h5>

<div class="text-center py-5">

    <i class="fas fa-stream fa-3x text-muted mb-3"></i>

    <h5 class="fw-bold">

        No Recent Activity

    </h5>

    <p class="text-muted mb-0">

        Performer portfolio uploads and profile updates will appear here.

    </p>

    </div>
</div>

<h4 class="fw-bold mb-3">Featured Performers</h4>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-semibold mb-3">Suggested for you</h5>
    <div class="row g-3">
        @forelse($recommendedPerformers as $p)
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:var(--ph-bg-input);">
                    <img src="{{ $p->profilePhotoUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($p->stage_name).'&background=6346ff&color=fff' }}" class="rounded-circle" width="48" height="48" style="object-fit:cover;">
                    <div class="flex-grow-1">
                        <h6 class="mb-0">{{ $p->stage_name }}</h6>
                        <small class="text-muted">{{ $p->categoryNames() }}</small>
                    </div>
                    <a href="{{ route('organizer.performers.show', $p) }}" class="btn btn-sm ph-btn-primary">View</a>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">No recommendations available.</p>
            <div class="mt-4">

    <a href="{{ route('organizer.performers.index') }}"
       class="btn ph-btn-primary">

        Browse All Performers

    </a>

</div>
        @endforelse
    </div>
</div>

<div class="ph-card p-4 mt-4">

    <div class="ph-card p-4 mt-4">

    <h5 class="fw-bold mb-3">

        Organizer Calendar

    </h5>

    <div class="text-center py-4">

        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>

        <p class="text-muted mb-0">

            Your scheduled events will appear here once the calendar feature is added.

        </p>

    </div>

</div>

<div class="ph-card p-4 mt-4">

    <h5 class="fw-bold mb-3">

        Upcoming Reminder

    </h5>


    @if($myEvents->isNotEmpty())

        <p class="mb-0">

            Your next scheduled event is

            <strong>{{ $myEvents->first()->title }}</strong>

            on

            <strong>{{ \Carbon\Carbon::parse($myEvents->first()->event_date)->format('F d, Y') }}</strong>.

        </p>

    @else

        <p class="text-muted mb-0">

            You don't have any upcoming events yet. Create one to get started.

        </p>

    @endif

</div>


@endsection
