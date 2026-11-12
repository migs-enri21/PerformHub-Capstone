@extends('layouts.app')

@section('title', $performer->stage_name ?: $user->fullName())

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Performer Profile</h2>
    <a href="{{ route('admin.users.show', $user) }}" class="btn ph-btn-outline btn-sm">Back to User Preview</a>
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
