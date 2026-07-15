@extends('layouts.app')

@section('title', $performer->stage_name)

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@include('partials.performer-profile-header', [
    'performer' => $performer,
    'editable' => false,
    'bookingUrl' => auth()->user()->hasLimitedAccess()
        ? null
        : route('organizer.bookings.create', [
            'performer' => $performer,
            'event' => request('event'),
        ]),
    'onboardingRoute' => auth()->user()->hasLimitedAccess()
        ? auth()->user()->onboardingRoute()
        : null,
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
        ->groupBy(fn ($item) => \App\Support\PortfolioFeed::groupKey($item))
        ->map(fn ($group) => $group->values());
@endphp
@if($portfolioGroups->isNotEmpty())
    <div class="ph-card p-4 mb-4">
        <h5 class="fw-semibold mb-3">Portfolio</h5>
        @include('partials.portfolio-feed', ['posts' => $portfolioGroups->values()])
    </div>
@endif

<div class="ph-card p-4">
    <h5 class="fw-semibold mb-3">Reviews</h5>
    @forelse($reviews as $r)
        <div class="mb-3 pb-3 border-bottom border-secondary-subtle">
            <div class="text-warning small mb-1">
                @for($i = 0; $i < $r->rating; $i++)
                    <i class="fas fa-star"></i>
                @endfor
            </div>
            <p class="small text-muted mb-0">{{ $r->comment }}</p>
        </div>
    @empty
        <p class="text-muted mb-0">No reviews yet.</p>
    @endforelse
</div>
@endsection
