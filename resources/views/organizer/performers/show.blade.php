@extends('layouts.app')

@section('title', $performer->stage_name)

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@php
    $bookingUrl = route('organizer.bookings.create', [
        'performer' => $performer,
        'event' => request('event'),
    ]);
    $onboardingRoute = null;

    if (auth()->user()->hasLimitedAccess()) {
        $bookingUrl = null;
        $onboardingRoute = auth()->user()->onboardingRoute();
    }
@endphp

@include('partials.performer-profile-header', [
    'performer' => $performer,
    'editable' => false,
    'bookingUrl' => $bookingUrl,
    'onboardingRoute' => $onboardingRoute,
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
    'memberSince' => $performer->user?->created_at,
    'emptyMessage' => 'This performer has not uploaded work samples yet.',
])

@endsection
