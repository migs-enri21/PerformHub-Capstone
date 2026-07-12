@props([
    'performer',
    'editable' => false,
])

@php
    $photoUrl = $performer->profilePhotoUrl()
        ?? 'https://ui-avatars.com/api/?name='.urlencode($performer->stage_name).'&background=6346ff&color=fff&size=256';
    $bannerStyle = $performer->bannerPhotoUrl()
        ? "background-image: url('".$performer->bannerPhotoUrl()."'); background-position: center ".($performer->banner_position_y ?? 50)."%;"
        : '';
    $rating = $performer->averageRating();
    $subtitle = collect([$performer->categoryNames(), $performer->shortLocation()])->filter()->implode(' · ');
    $tags = $performer->displayTags();
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
        <div class="d-flex flex-column flex-md-row gap-3 gap-md-4">
            <div class="performer-profile-avatar-wrap flex-shrink-0">
                <img
                    src="{{ $photoUrl }}"
                    alt=""
                    class="performer-profile-avatar rounded-circle"
                    width="120"
                    height="120"
                    onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($performer->stage_name) }}&background=6346ff&color=fff&size=256';"
                >
                @if($editable)
                    <a href="{{ route('performer.profile.edit') }}#photo" class="performer-profile-avatar-edit" aria-label="Edit profile photo">
                        <i class="fas fa-camera"></i>
                    </a>
                @endif
            </div>

            <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 mb-2">
                    <div>
                        <h2 class="fw-bold mb-1 performer-profile-name">{{ $performer->stage_name }}</h2>
                        @if($subtitle)
                            <p class="text-muted mb-0 performer-profile-subtitle">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @if($performer->is_verified_badge)
                            <span class="profile-verified-pill">
                                <i class="fas fa-circle-check me-1"></i> Verified
                            </span>
                        @endif
                        @if($rating > 0)
                            <span class="profile-rating-pill">
                                <i class="fas fa-star me-1"></i> {{ number_format($rating, 1) }}
                            </span>
                        @endif
                    </div>
                </div>

                <p class="performer-profile-bio mb-0">
                    {{ $performer->bio ?: 'Add a bio to tell organizers about your experience and style.' }}
                </p>
            </div>
        </div>

        @if(count($tags))
            <div class="performer-profile-tags mt-4">
                @foreach($tags as $tag)
                    <span class="profile-tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
