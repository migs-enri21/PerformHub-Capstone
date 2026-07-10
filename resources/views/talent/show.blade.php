@extends('layouts.app')

@section('title', $performer->stage_name)

@section('sidebar')
@include('partials.role-sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        @include('partials.performer-profile-header', [
            'performer' => $performer,
            'editable' => false,
        ])

        <div class="ph-card p-4 mb-4">
            <h5 class="fw-semibold">About</h5>
            <p class="text-muted mb-0">{{ $performer->bio ?? 'No bio provided.' }}</p>
        </div>

        @if($portfolioGroups->isNotEmpty())
            <h5 class="fw-semibold mb-3">Portfolio Posts</h5>
            @include('partials.portfolio-feed', ['posts' => $portfolioGroups->values()])
        @endif

        <div class="d-flex flex-wrap gap-2 mt-4">
            @if(auth()->user()->isOrganizer() && auth()->id() !== $performer->user_id)
                @if(auth()->user()->hasLimitedAccess())
                    <a href="{{ auth()->user()->onboardingRoute() }}" class="btn ph-btn-primary">
                        <i class="fas fa-lock me-1"></i> Complete sign-up to book
                    </a>
                @else
                    <a href="{{ route('organizer.bookings.create', $performer) }}" class="btn ph-btn-primary">Send Booking Request</a>
                @endif
            @endif
            <a href="{{ url()->previous() }}" class="btn ph-btn-outline">Back</a>
        </div>
    </div>
</div>
@endsection
