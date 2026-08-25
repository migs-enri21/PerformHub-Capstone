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
    $portfolioGroups = $performer->portfolios
        ->sortByDesc('created_at')
        ->groupBy(function ($item) {
            return \App\Support\PortfolioFeed::groupKey($item);
        })
        ->map(function ($group) {
            return $group->values();
        });
@endphp
@if($portfolioGroups->isNotEmpty())
    <div class="ph-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">Portfolio</h5>
        @include('partials.portfolio-feed', ['posts' => $portfolioGroups->values()])
    </div>
@endif

@endsection
