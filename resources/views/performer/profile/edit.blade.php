@extends('layouts.app')

@section('title', 'Edit Profile')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Edit Profile</h2>
    <a href="{{ route('performer.profile.show') }}" class="btn ph-btn-outline btn-sm">
        <i class="fas fa-eye me-1"></i> View Profile
    </a>
</div>
<form method="POST" action="{{ route('performer.profile.update') }}" enctype="multipart/form-data" id="profileForm">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="ph-card p-4 profile-photo-card" id="photo">
                @include('partials.profile-photo-upload', [
                    'currentUrl' => $profile->profile_photo ? asset('storage/'.$profile->profile_photo) : null,
                    'fallbackName' => $profile->stage_name,
                ])
            </div>
            <div class="ph-card p-4 mt-4" id="banner">
                <h5 class="fw-semibold mb-3">Banner Image</h5>
                @if($profile->banner_photo)
                    <img src="{{ asset('storage/'.$profile->banner_photo) }}" alt="" class="w-100 rounded mb-3 performer-banner-preview">
                @else
                    <div class="performer-banner-preview performer-banner-preview--empty rounded mb-3"></div>
                @endif
                <label class="form-label text-muted small" for="banner_photo_input">Upload banner (JPG, PNG, WEBP · max 5 MB)</label>
                <input type="file" name="banner_photo" id="banner_photo_input" class="form-control ph-input" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>
        </div>
        <div class="col-lg-8">
            <div class="ph-card p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Stage Name</label>
                        <input type="text" name="stage_name" class="form-control ph-input" value="{{ old('stage_name', $profile->stage_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Category</label>
                        <select name="category_id" class="form-select ph-input">
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $profile->category_id) == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Genre</label>
                        @include('partials.genre-select', ['value' => $profile->genre])
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Rate (₱)</label>
                        <input type="number" step="0.01" name="rate" class="form-control ph-input" value="{{ old('rate', $profile->rate) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Location</label>
                        @include('partials.location-select', [
                            'region' => $profile->region,
                            'city' => $profile->city,
                            'barangay' => $profile->barangay,
                        ])
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted small">Bio</label>
                        <textarea name="bio" class="form-control ph-input" rows="4">{{ old('bio', $profile->bio) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="ph-card p-4 mt-4">
                <h5 class="fw-semibold mb-1">Social Media</h5>
                <p class="text-muted small mb-3">Add profile links and follower counts. Enter the full number (e.g. <strong>19000</strong> shows as 19K on your profile).</p>
                <div class="row g-3">
                    @php
                        $socialFields = [
                            'facebook' => ['label' => 'Facebook', 'metric' => 'Followers', 'count' => 'social_facebook_followers'],
                            'instagram' => ['label' => 'Instagram', 'metric' => 'Followers', 'count' => 'social_instagram_followers'],
                            'youtube' => ['label' => 'YouTube', 'metric' => 'Subscribers', 'count' => 'social_youtube_subscribers'],
                            'tiktok' => ['label' => 'TikTok', 'metric' => 'Followers', 'count' => 'social_tiktok_followers'],
                            'twitter' => ['label' => 'Twitter/X', 'metric' => 'Followers', 'count' => 'social_twitter_followers'],
                        ];
                    @endphp
                    @foreach($socialFields as $field => $meta)
                        <div class="col-12">
                            <div class="social-edit-row p-3 rounded">
                                <label class="form-label text-muted small mb-2">{{ $meta['label'] }}</label>
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <input type="url" name="social_{{ $field }}" class="form-control ph-input" value="{{ old('social_'.$field, $profile->{'social_'.$field}) }}" placeholder="https://">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="number" min="0" name="{{ $meta['count'] }}" class="form-control ph-input" value="{{ old($meta['count'], $profile->{$meta['count']}) }}" placeholder="{{ $meta['metric'] }}">
                                            <span class="input-group-text social-metric-suffix">{{ $meta['metric'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn ph-btn-primary mt-4">Save Profile</button>
        </div>
    </div>
</form>
@endsection
