@props([
    'performer',
    'editable' => false,
    'bookingUrl' => null,
    'bookingMessage' => null,
    'onboardingRoute' => null,
])

@php
    $photoUrl = $performer->profilePhotoUrl()
        ?? 'https://ui-avatars.com/api/?name='.urlencode($performer->stage_name).'&background=6346ff&color=fff&size=256';
    $bannerStyle = $performer->bannerPhotoUrl()
        ? "background-image: url('".$performer->bannerPhotoUrl()."'); background-position: center ".($performer->banner_position_y ?? 50)."%;"
        : '';
    $rating = 0; // reviews/ratings not implemented yet
@endphp

<div class="performer-profile-card ph-card mb-4">
    <div class="performer-profile-banner" style="{{ $bannerStyle }}">
        @if($editable)
            <a href="{{ route('performer.profile.edit') }}#banner" class="btn btn-sm performer-profile-banner-edit">
                <i class="fas fa-pen me-1"></i> Edit Banner
            </a>
        @endif
    </div>

    <div class="performer-profile-body">
        <div class="performer-profile-layout">
            <div class="performer-profile-layout-photo">
                <div class="performer-profile-avatar-wrap">
                    <img
                        src="{{ $photoUrl }}"
                        alt=""
                        class="performer-profile-avatar rounded-circle"
                        width="200"
                        height="200"
                        onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($performer->stage_name) }}&background=6346ff&color=fff&size=256';"
                    >
                    @if($editable)
                        <a href="{{ route('performer.profile.edit') }}#photo" class="performer-profile-avatar-edit" aria-label="Edit profile photo">
                            <i class="fas fa-camera"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="performer-profile-layout-main">
                <div class="d-flex flex-column flex-lg-row align-items-start justify-content-lg-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h2 class="fw-bold mb-0 performer-profile-name">{{ $performer->stage_name }}</h2>
                            @if($performer->is_verified_badge)
                                <span class="profile-verified-pill">
                                    <i class="fas fa-circle-check me-1"></i> Verified
                                </span>
                            @endif
                        </div>
                        <div class="performer-profile-facts">
                            @foreach($performer->categories as $category)
                                <span class="performer-profile-category"><i class="fas fa-microphone"></i> {{ $category->name }}</span>
                            @endforeach
                            @if($performer->shortLocation())
                                <span><i class="fas fa-location-dot"></i> {{ $performer->shortLocation() }}</span>
                            @endif
                        </div>
                        @if($performer->specialtyLabel())
                            <p class="performer-profile-detail mb-1">
                                <i class="fas fa-guitar"></i> {{ $performer->specialtyLabel() }}
                            </p>
                        @endif
                        @if($performer->genreLabel())
                            <p class="performer-profile-detail mb-0">
                                <i class="fas fa-music"></i> {{ $performer->genreLabel() }}
                            </p>
                        @endif
                        @if($bookingUrl || $onboardingRoute || $bookingMessage)
                            <div class="profile-booking-bar d-flex flex-wrap align-items-center gap-2 mt-3">
                                @foreach($performer->rateLines() as $line)
                                    <span class="profile-rate-pill">{{ $line }}</span>
                                @endforeach
                                @if($onboardingRoute)
                                    <a href="{{ $onboardingRoute }}" class="btn ph-btn-primary btn-sm">
                                        <i class="fas fa-lock me-1"></i> Pending Verification
                                    </a>
                                @elseif($bookingUrl)
                                    <a href="{{ $bookingUrl }}" class="btn ph-btn-primary btn-sm">
                                        Send Booking Request
                                    </a>
                                @elseif($bookingMessage)
                                    <span class="text-success small fw-semibold">{{ $bookingMessage }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @if($rating > 0)
                            <span class="profile-rating-pill">
                                <i class="fas fa-star me-1"></i> {{ number_format($rating, 1) }}
                            </span>
                        @endif
                    </div>
                </div>

                <p class="performer-profile-bio mb-0 mt-3">
                    @if($performer->bio)
                        {{ $performer->bio }}
                    @else
                        Add a bio to tell organizers about your experience and style.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
