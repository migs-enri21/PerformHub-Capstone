@props([
    'organizer',
    'editable' => false,
])

@php
    $photoUrl = $organizer->profilePhotoUrl()
        ?? 'https://ui-avatars.com/api/?name='.urlencode($organizer->organization_name).'&background=6346ff&color=fff&size=256';
    $bannerStyle = $organizer->bannerPhotoUrl()
        ? "background-image: url('".$organizer->bannerPhotoUrl()."'); background-position: center ".($organizer->banner_position_y ?? 50)."%;"
        : '';
    $subtitle = collect([$organizer->organization_type ? ucfirst($organizer->organization_type) : null, $organizer->shortLocation()])->filter()->implode(' · ');
@endphp

<div class="performer-profile-card ph-card mb-4">
    <div class="performer-profile-banner" style="{{ $bannerStyle }}">
        @if($editable)
            <a href="{{ route('organizer.profile.edit') }}#banner" class="btn btn-sm performer-profile-banner-edit">
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
                    width="200"
                    height="200"
                    onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($organizer->organization_name) }}&background=6346ff&color=fff&size=256';"
                >
                @if($editable)
                    <a href="{{ route('organizer.profile.edit') }}#photo" class="performer-profile-avatar-edit" aria-label="Edit profile photo">
                        <i class="fas fa-camera"></i>
                    </a>
                @endif
            </div>

            <div class="flex-grow-1 min-w-0">
            <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 mb-2">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h2 class="fw-bold mb-0 performer-profile-name">{{ $organizer->organization_name }}</h2>
                        @if(optional($organizer->user)->is_verified)
                            <span class="profile-verified-pill">
                                <i class="fas fa-circle-check me-1"></i> Verified
                            </span>
                        @endif
                    </div>
                    @if($subtitle)
                        <p class="text-muted mb-0 performer-profile-subtitle">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

                <p class="performer-profile-bio mb-0">
                    {{ $organizer->bio ?: 'Add a bio to tell performers about your organization.' }}
                </p>
            </div>
        </div>
    </div>
</div>
