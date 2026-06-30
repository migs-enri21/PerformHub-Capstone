@extends('layouts.app')

@section('title', 'Organizer Dashboard')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
@include('partials.onboarding-banner')

<h2 class="fw-bold mb-1">Welcome, {{ $profile?->organization_name ?? auth()->user()->name }}</h2>
<p class="text-muted mb-4">
    @if(auth()->user()->hasLimitedAccess())
        Manage your events and discover talent — complete sign-up to book performers.
    @else
        Manage your events and discover talent
    @endif
</p>

<div class="row g-4 mb-4">
    <div class="col-md-4"><div class="ph-card p-4 stat-card"><h3 class="fw-bold mb-0">{{ $pendingBookings }}</h3><p class="text-muted small mb-0">Pending Bookings</p></div></div>
    <div class="col-md-4"><div class="ph-card p-4 stat-card"><h3 class="fw-bold mb-0">{{ $activeBookings }}</h3><p class="text-muted small mb-0">Active Bookings</p></div></div>
    <div class="col-md-4"><div class="ph-card p-4 stat-card"><h3 class="fw-bold mb-0">{{ $recommendedPerformers->count() }}</h3><p class="text-muted small mb-0">Recommendations</p></div></div>
</div>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-semibold mb-1">Latest from Performers</h5>
    <p class="text-muted small mb-4">Browse portfolio posts and send booking requests to performers you like.</p>
    @include('partials.portfolio-feed', [
        'posts' => $feedPosts,
        'emptyMessage' => 'No performer posts yet. Check back soon or browse all performers.',
    ])
</div>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-semibold mb-3">Recommended Performers <small class="text-muted">(without ratings)</small></h5>
    <div class="row g-3">
        @forelse($recommendedPerformers as $p)
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:var(--ph-bg-input);">
                    <img src="{{ $p->profile_photo ? asset('storage/'.$p->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode($p->stage_name).'&background=6346ff&color=fff' }}" class="rounded-circle" width="48" height="48" style="object-fit:cover;">
                    <div class="flex-grow-1">
                        <h6 class="mb-0">{{ $p->stage_name }}</h6>
                        <small class="text-muted">{{ $p->category?->name }}</small>
                    </div>
                    <a href="{{ route('organizer.performers.show', $p) }}" class="btn btn-sm ph-btn-primary">View</a>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">No recommendations available.</p>
        @endforelse
    </div>
</div>

<a href="{{ route('organizer.performers.index') }}" class="btn ph-btn-primary">Browse All Performers</a>
@endsection
