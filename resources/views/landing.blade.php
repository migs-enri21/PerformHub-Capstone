@extends('layouts.guest')

@section('title', 'PerformHub - Connect Performers & Event Organizers')

@section('content')
<nav class="navbar navbar-expand-lg navbar-dark navbar-ph fixed-top" style="z-index: 1030;">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('home') }}"><img src="{{ asset('images/logo.png') }}" alt="PerformHub" height="32" width="32" class="me-2 rounded-circle" style="object-fit: cover;">PerformHub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navLanding">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navLanding">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <li class="nav-item"><a class="nav-link" href="#categories">Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                @guest
                    <li class="nav-item"><a class="btn ph-btn-outline btn-sm" href="{{ route('login') }}">Sign In</a></li>
                    <li class="nav-item"><a class="btn ph-btn-primary btn-sm" href="{{ route('register') }}">Get Started</a></li>
                @else
                    <li class="nav-item"><a class="btn ph-btn-primary btn-sm" href="{{ auth()->user()->dashboardRoute() }}">Dashboard</a></li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

<section class="hero-landing text-white">
    <div class="container pt-5">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-3 fw-bold mb-4">Discover Talent.<br>Book the Perfect Performance.</h1>
                <p class="lead text-white-50 mb-4">PerformHub connects talented performers with event organizers for seamless entertainment booking, auditions, and event coordination.</p>
                <div class="d-flex flex-wrap gap-3">
                    @guest
                        <a href="{{ route('register', ['role' => 'organizer']) }}" class="btn ph-btn-primary btn-lg">Find Performers</a>
                        <a href="{{ route('register', ['role' => 'performer']) }}" class="btn ph-btn-outline btn-lg text-white">Join as Performer</a>
                    @else
                        <a href="{{ auth()->user()->dashboardRoute() }}" class="btn ph-btn-primary btn-lg">Go to Dashboard</a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</section>

<section id="categories" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Browse by Category</h2>
            <p class="text-muted">Find performers across every entertainment genre</p>
        </div>
        <div class="row g-4">
            @forelse($categories as $category)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="ph-card p-4 text-center h-100">
                        <div class="category-icon"><i class="fas {{ $category->icon ?? 'fa-star' }}"></i></div>
                        <h6 class="fw-semibold">{{ $category->name }}</h6>
                        <p class="text-muted small mb-0">{{ Str::limit($category->description, 60) }}</p>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">Categories coming soon.</div>
            @endforelse
        </div>
    </div>
</section>

<section class="py-5" style="background: var(--ph-bg-card);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Performers</h2>
            <p class="text-muted">Top talent ready for your next event</p>
        </div>
        <div class="row g-4">
            @forelse($featuredPerformers as $performer)
                <div class="col-md-6 col-lg-4">
                    <div class="ph-card p-4 h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="{{ $performer->profilePhotoUrl() ?? 'https://ui-avatars.com/api/?name='.urlencode($performer->stage_name).'&background=6346ff&color=fff' }}" class="performer-avatar" alt="">
                            <div>
                                <h6 class="mb-0 fw-semibold">
                                    {{ $performer->stage_name }}
                                    @if($performer->is_verified_badge)<i class="fas fa-circle-check verified-badge ms-1"></i>@endif
                                </h6>
                                <small class="text-muted">{{ $performer->categoryNames() ?: 'Performer' }} · {{ $performer->location ?? 'Philippines' }}</small>
                            </div>
                        </div>
                        <p class="text-muted small">{{ Str::limit($performer->bio, 100) ?: 'Talented performer available for bookings.' }}</p>
                        @if($performer->rate)<p class="mb-0 fw-semibold text-primary">₱{{ number_format($performer->rate, 2) }}/event</p>@endif
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">No featured performers yet.</div>
            @endforelse
        </div>
    </div>
</section>

<section id="how-it-works" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">How PerformHub Works</h2>
            <p class="text-muted">Book talent in three simple steps</p>
        </div>
        <div class="row g-4">
            @foreach([['icon'=>'fa-search','title'=>'Discover','desc'=>'Search and filter performers by category, genre, and availability.'],['icon'=>'fa-paper-plane','title'=>'Request','desc'=>'Send booking requests with event details and requirements.'],['icon'=>'fa-check-circle','title'=>'Book','desc'=>'Accept bookings, manage contracts, and complete events.']] as $step)
                <div class="col-md-6 col-lg-3">
                    <div class="ph-card p-4 text-center h-100">
                        <div class="category-icon"><i class="fas {{ $step['icon'] }}"></i></div>
                        <h6 class="fw-semibold">{{ $step['title'] }}</h6>
                        <p class="text-muted small mb-0">{{ $step['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>


<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose PerformHub</h2>
        </div>
        <div class="row g-4">
            @foreach([['icon'=>'fa-shield-halved','title'=>'Verified Performers','desc'=>'Admin-verified profiles with badge system.'],['icon'=>'fa-calendar-check','title'=>'Smart Scheduling','desc'=>'Availability calendars synced with Google Calendar.'],['icon'=>'fa-file-contract','title'=>'Contract Management','desc'=>'Upload and confirm contracts digitally.']] as $f)
                <div class="col-md-6 col-lg-3">
                    <div class="ph-card p-4 h-100">
                        <i class="fas {{ $f['icon'] }} text-primary fs-4 mb-3"></i>
                        <h6 class="fw-semibold">{{ $f['title'] }}</h6>
                        <p class="text-muted small mb-0">{{ $f['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="cta-section p-5 text-center text-white">
            <h2 class="fw-bold mb-3">Ready to take the stage?</h2>
            <p class="mb-4 opacity-75">Join thousands of performers and organizers on PerformHub today.</p>
            @guest
                <a href="{{ route('register') }}" class="btn btn-light btn-lg fw-semibold px-5">Get Started Free</a>
            @else
                <a href="{{ auth()->user()->dashboardRoute() }}" class="btn btn-light btn-lg fw-semibold px-5">Go to Dashboard</a>
            @endguest
        </div>
    </div>
</section>

<footer class="py-4 border-top" style="border-color: var(--ph-border) !important;">
    <div class="container text-center text-muted small">
        <p class="mb-0">&copy; {{ date('Y') }} PerformHub. All rights reserved.</p>
    </div>
</footer>
@endsection
