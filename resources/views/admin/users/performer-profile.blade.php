@extends('layouts.app')

@section('title', $performer->stage_name ?: $user->fullName())

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Performer Profile</h2>
    <div class="d-flex align-items-center gap-2">
        @if(!$performer->user?->isOrganizer() && $performer->user?->profile_checked_by_admin)
            <form method="POST" action="{{ route('admin.users.uncheck-profile', $performer->user) }}">
                @csrf
                <button type="submit" class="btn btn-outline-warning admin-user-action-btn">Uncheck Profile</button>
            </form>
        @elseif(!$performer->user?->isOrganizer() && !$performer->user?->is_verified)
            <form method="POST" action="{{ route('admin.users.check-profile', $performer->user) }}">
                @csrf
                <button type="submit" class="btn ph-btn-primary admin-user-action-btn" title="Mark personal details as reviewed">
                    Check Profile
                </button>
            </form>
        @endif
        <a href="{{ route('admin.users.show', $user) }}" class="btn ph-btn-outline btn-sm">Back to User Preview</a>
    </div>
</div>

@php
    $memberSince = $performer->user?->created_at;
@endphp

@include('partials.performer-profile-header', [
    'performer' => $performer,
    'editable' => false,
    'bookingUrl' => null,
    'bookingMessage' => null,
    'onboardingRoute' => null,
])

@if($performer->socialLinks())
    @include('partials.social-media-section', ['performer' => $performer])
@endif

<div class="ph-card p-4 mb-4" id="availability">
    <h5 class="fw-semibold mb-3">Availability Calendar</h5>
    @include('partials.availability-calendar', [
        'schedules' => $calendar['schedules'],
        'bookingCalendar' => $calendar['bookingCalendar'],
        'googleBusy' => $calendar['googleBusy'],
        'editable' => false,
    ])
</div>

@php
    $portfolioGroups = \App\Support\PortfolioFeed::groupItems($performer->portfolios);
@endphp

@include('partials.portfolio-work-samples', [
    'groups' => $portfolioGroups,
    'memberSince' => $memberSince,
    'emptyMessage' => 'This performer has not uploaded work samples yet.',
])
@endsection
