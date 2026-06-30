@extends('layouts.app')

@section('title', $performer->stage_name)

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="ph-card p-4 text-center">
            <img src="{{ $performer->profile_photo ? asset('storage/'.$performer->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode($performer->stage_name).'&background=6346ff&color=fff&size=128' }}" class="rounded-circle mb-3" width="128" height="128" style="object-fit:cover;">
            <h4 class="fw-bold">{{ $performer->stage_name }} @if($performer->is_verified_badge)<i class="fas fa-circle-check verified-badge"></i>@endif</h4>
            <p class="text-muted">{{ $performer->category?->name }} · {{ $performer->genre }}</p>
            @if($performer->rate)<p class="fw-semibold">₱{{ number_format($performer->rate, 2) }}/event</p>@endif
            @if(auth()->user()->hasLimitedAccess())
                <a href="{{ auth()->user()->onboardingRoute() }}" class="btn ph-btn-primary w-100 mb-2">
                    <i class="fas fa-lock me-1"></i> Complete sign-up to book
                </a>
            @else
                <a href="{{ route('organizer.bookings.create', $performer) }}" class="btn ph-btn-primary w-100 mb-2">Send Booking Request</a>
            @endif
            <a href="{{ route('messages.show', $performer->user) }}" class="btn ph-btn-outline w-100">Message</a>
        </div>
    </div>
    <div class="col-lg-8">
        @if($performer->socialLinks())
            @include('partials.social-media-section', ['performer' => $performer])
        @endif
        <div class="ph-card p-4 mb-4"><h5 class="fw-semibold">About</h5><p class="text-muted mb-0">{{ $performer->bio ?? 'No bio provided.' }}</p></div>
        <div class="ph-card p-4 mb-4" id="availability">
            <h5 class="fw-semibold mb-3">Availability Calendar</h5>
            @include('partials.availability-calendar', [
                'schedules' => $performer->availabilitySchedules,
                'bookingCalendar' => $performer->bookings,
                'editable' => false,
            ])
        </div>
        <div class="ph-card p-4 mb-4">
            <h5 class="fw-semibold mb-3">Portfolio</h5>
            @php
                $portfolioGroups = $performer->portfolios
                    ->sortByDesc('created_at')
                    ->groupBy(fn ($item) => \App\Support\PortfolioFeed::groupKey($item))
                    ->map(fn ($group) => $group->values());
            @endphp
            @include('partials.portfolio-feed', ['posts' => $portfolioGroups->values()])
        </div>
        <div class="ph-card p-4">
            <h5 class="fw-semibold mb-3">Reviews</h5>
            @forelse($reviews as $r)
                <div class="mb-2"><div class="text-warning small">@for($i=0;$i<$r->rating;$i++)<i class="fas fa-star"></i>@endfor</div><p class="small text-muted mb-0">{{ $r->comment }}</p></div>
            @empty
                <p class="text-muted mb-0">No reviews yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
