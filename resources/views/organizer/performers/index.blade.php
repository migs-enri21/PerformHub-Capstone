@extends('layouts.app')

@section('title', 'Find Performers')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Search Performers</h2>

@if($selectedEvent)
<div class="alert alert-info mb-4">
    <strong>Recommended performers for</strong>
    {{ $selectedEvent->title }}

    <br>

    <small>
        <strong>Category:</strong>
        {{ $selectedEvent->eventType->name ?? 'N/A' }}
    </small>

    <br>

    <small>
        <strong>Event Date:</strong>
        {{ \Carbon\Carbon::parse($selectedEvent->event_date)->format('F d, Y') }}
    </small>

    <br>

    <small class="text-muted">Recommendations are based on the selected event's category and performer availability.</small>
</div>
@endif

<div class="ph-card p-4 mb-4">
    <form method="GET" class="row g-3">
        <div class="col-md-3"><input type="text" name="search" class="form-control ph-input" placeholder="Search..." value="{{ request('search') }}"></div>
        <div class="col-md-2">
            <select name="category_id" class="form-select ph-input">
                <option value="">All Categories</option>
                @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2">@include('partials.genre-select', ['value' => request('genre'), 'placeholder' => 'All Genres'])</div>
        <div class="col-md-2"><input type="number" name="min_rating" class="form-control ph-input" placeholder="Min Rating" min="1" max="5" value="{{ request('min_rating') }}"></div>
        <div class="col-md-2"><input type="date" name="available_date" class="form-control ph-input" value="{{ request('available_date') }}"></div>
        <div class="col-md-1"><button class="btn ph-btn-primary w-100">Filter</button></div>
    </form>
</div>

<div class="row g-4">
    @forelse($performers as $p)
        <div class="col-md-6 col-lg-4">
            <div class="ph-card p-4 h-100">
                <div class="d-flex gap-3 mb-3">
                    <img src="{{ $p->profile_photo ? asset('storage/'.$p->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode($p->stage_name).'&background=6346ff&color=fff' }}" class="performer-avatar" alt="">
                    <div>
                        <h6 class="mb-0">{{ $p->stage_name }} @if($p->is_verified_badge)<i class="fas fa-circle-check verified-badge"></i>@endif</h6>
                        <small class="text-muted">{{ $p->category?->name }} · {{ $p->genre }}</small>
                        <div class="text-warning small">@for($i=0;$i<round($p->averageRating());$i++)<i class="fas fa-star"></i>@endfor</div>
                    </div>
                </div>
                <p class="text-muted small">{{ Str::limit($p->bio, 80) }}</p>
                <a href="{{ route('organizer.performers.show', $p) }}" class="btn ph-btn-primary btn-sm">View Profile</a>
                <a href="{{ route('organizer.bookings.create', $p) }}"
   class="btn ph-btn-primary btn-sm flex-fill">
    Send Booking Request
</a>
            </div>
        </div>
    @empty
        <div class="col-12 text-muted">No performers found.</div>
    @endforelse
</div>
{{ $performers->links() }}
@endsection
