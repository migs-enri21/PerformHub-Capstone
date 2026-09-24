@extends('layouts.app')

@section('title', 'User Preview')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">User Preview</h2>
    <a href="{{ route('admin.users.index') }}" class="btn ph-btn-outline">Back to Users</a>
</div>

<div class="ph-card p-4 mb-4">
    @php
        $isOrganizer = $user->isOrganizer();
        $profileComplete = $isOrganizer || $user->onboarding_step >= \App\Models\User::ONBOARDING_VERIFICATION;
        $verificationComplete = $user->is_verified;
        $onboardingComplete = $profileComplete && $verificationComplete;
    @endphp
    <div class="row g-4">
        <div class="col-md-4">
            <h5 class="fw-bold">Basic Details</h5>
            <dl class="row">
                <dt class="col-sm-4 text-muted">Name</dt>
                <dd class="col-sm-8">
                    <button type="button" class="btn btn-link p-0 fw-semibold text-decoration-none" data-bs-toggle="modal" data-bs-target="#profilePreviewModal" title="Click to preview {{ $user->fullName() }} profile" data-bs-toggle-tooltip="tooltip">
                        {{ $user->fullName() }}
                    </button>
                </dd>

                <dt class="col-sm-4 text-muted">Email</dt>
                <dd class="col-sm-8">{{ $user->email }}</dd>

                <dt class="col-sm-4 text-muted">Role</dt>
                <dd class="col-sm-8">{{ ucfirst($user->role) }}</dd>

                <dt class="col-sm-4 text-muted">Status</dt>
                <dd class="col-sm-8">{{ $user->is_active ? 'Active' : 'Suspended' }}</dd>

                <dt class="col-sm-4 text-muted">Verified</dt>
                <dd class="col-sm-8">{{ $user->is_verified ? 'Yes' : 'No' }}</dd>

            </dl>
        </div>

        <div class="col-md-4">
            <h5 class="fw-bold">Profile Info</h5>
            @if($user->isPerformer() && $user->performerProfile)
                <dl class="row">
                    <dt class="col-sm-4 text-muted">Stage Name</dt>
                    <dd class="col-sm-8">{{ $user->performerProfile->stage_name }}</dd>

                    <dt class="col-sm-4 text-muted">Genre</dt>
                    <dd class="col-sm-8">{{ $user->performerProfile->genreLabel() !== '' ? $user->performerProfile->genreLabel() : '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Category</dt>
                    <dd class="col-sm-8">
                        {{ $user->performerProfile->categories->pluck('name')->join(', ') ?: '—' }}
                    </dd>

                    <dt class="col-sm-4 text-muted">Location</dt>
                    <dd class="col-sm-8">{{ $user->performerProfile->shortLocation() }}</dd>
                </dl>
            @elseif($user->isOrganizer() && $user->organizerProfile)
                <dl class="row">
                    <dt class="col-sm-4 text-muted">Organization</dt>
                    <dd class="col-sm-8">
                        {{ [
                            'agency' => 'Agency',
                            'freelancer' => 'Freelancer',
                        ][$user->organizerProfile->organization_type ?? ''] ?? 'N/A' }}
                    </dd>

                    <dt class="col-sm-4 text-muted">Location</dt>
                    <dd class="col-sm-8">{{ $user->organizerProfile->shortLocation() }}</dd>
                </dl>
            @endif
        </div>

        <div class="col-md-4">
            <h5 class="fw-bold">Onboarding Progress</h5>
            <div class="d-flex flex-column gap-3 mt-3">
                @unless($isOrganizer)
                <div class="d-flex align-items-center gap-2">
                    <i class="fas {{ $profileComplete ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' }}"></i>
                    <div>
                        <div class="fw-semibold">1. Profile Details</div>
                        <small class="text-muted">{{ $profileComplete ? 'Complete' : 'Incomplete' }}</small>
                    </div>
                </div>
                @endunless
                <div class="d-flex align-items-center gap-2">
                    <i class="fas {{ $verificationComplete ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' }}"></i>
                    <div>
                        <div class="fw-semibold">{{ $isOrganizer ? '1' : '2' }}. Admin Verification</div>
                        <small class="text-muted">{{ $verificationComplete ? 'Complete' : 'Incomplete' }}</small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="profilePreviewModal" tabindex="-1" aria-labelledby="profilePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="profilePreviewModalLabel">User Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 class="fw-bold mb-3">{{ $user->fullName() }}</h5>
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Email</dt>
                    <dd class="col-7">{{ $user->email }}</dd>
                    <dt class="col-5 text-muted">Role</dt>
                    <dd class="col-7">{{ ucfirst($user->role) }}</dd>
                    <dt class="col-5 text-muted">Status</dt>
                    <dd class="col-7">{{ $user->is_active ? 'Active' : 'Suspended' }}</dd>
                    <dt class="col-5 text-muted">Verified</dt>
                    <dd class="col-7">{{ $user->is_verified ? 'Yes' : 'No' }}</dd>
                    @if($user->isPerformer() && $user->performerProfile)
                        <dt class="col-5 text-muted">Stage Name</dt>
                        <dd class="col-7">{{ $user->performerProfile->stage_name ?: '—' }}</dd>
                        <dt class="col-5 text-muted">Categories</dt>
                        <dd class="col-7">{{ $user->performerProfile->categories->pluck('name')->join(', ') ?: '—' }}</dd>
                        <dt class="col-5 text-muted">Specialty</dt>
                        <dd class="col-7">{{ $user->performerProfile->specialtyLabel() ?: '—' }}</dd>
                        <dt class="col-5 text-muted">Genre</dt>
                        <dd class="col-7">{{ $user->performerProfile->genreLabel() ?: '—' }}</dd>
                        <dt class="col-5 text-muted">Rate</dt>
                        <dd class="col-7">{{ $user->performerProfile->rate !== null ? '₱'.number_format((float) $user->performerProfile->rate, 2) : '—' }}</dd>
                    @elseif($user->isOrganizer() && $user->organizerProfile)
                        <dt class="col-5 text-muted">Organization</dt>
                        <dd class="col-7">{{ $user->organizerProfile->organization_name ?: '—' }}</dd>
                    @endif
                    <dt class="col-5 text-muted">Location</dt>
                    <dd class="col-7">{{ $user->isPerformer() ? $user->performerProfile?->shortLocation() : $user->organizerProfile?->shortLocation() }}</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                @if(!$profileComplete)
                    <form method="POST" action="{{ route('admin.users.check-profile', $user) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn ph-btn-primary">Check Profile</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.uncheck-profile', $user) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning">Uncheck Profile</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="onboardingProgressModal" tabindex="-1" aria-labelledby="onboardingProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="min-height: 320px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="onboardingProgressModalLabel">Onboarding Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column">
                <p class="fw-semibold mb-3">{{ $user->fullName() }}</p>
                @unless($isOrganizer)
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fas {{ $profileComplete ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' }}"></i>
                    <div>
                        <div class="fw-semibold">1. Profile Details</div>
                        <small class="text-muted">{{ $profileComplete ? 'Complete' : 'Incomplete' }}</small>
                    </div>
                </div>
                @endunless
                <div class="d-flex align-items-center gap-2">
                    <i class="fas {{ $verificationComplete ? 'fa-circle-check text-success' : 'fa-circle-xmark text-danger' }}"></i>
                    <div>
                        <div class="fw-semibold">{{ $isOrganizer ? '1' : '2' }}. Admin Verification</div>
                        <small class="text-muted">{{ $verificationComplete ? 'Complete' : 'Incomplete' }}</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer mt-auto">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                @if(!$isOrganizer && !$profileComplete)
                    <form method="POST" action="{{ route('admin.users.check-profile', $user) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn ph-btn-primary">Check Profile</button>
                    </form>
                @elseif(!$isOrganizer)
                    <form method="POST" action="{{ route('admin.users.uncheck-profile', $user) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning">Uncheck Profile</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-bold mb-3">Verification Documents</h5>
    @if($user->verificationDocuments->isEmpty())
        <p class="text-muted">No verification documents uploaded yet.</p>
    @else
        <div class="row gy-3">
            @foreach($user->verificationDocuments as $document)
                <div class="col-md-6">
                    <div class="ph-card p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 text-capitalize">{{ str_replace('_', ' ', $document->document_type) }}</h6>
                                <p class="text-muted small mb-0">{{ $document->original_name }}</p>
                            </div>
                            <span class="badge bg-secondary">{{ $document->created_at->diffForHumans() }}</span>
                        </div>
                        @php
                            $path = $document->file_path;
                            $bucket = $user->isPerformer() ? 'performer-files' : 'organizer-files';
                            $url = (new App\Services\SupabaseStorageService)->url($bucket, $path);
                            $extension = pathinfo($path, PATHINFO_EXTENSION);
                        @endphp

                        @if(in_array(strtolower($extension), ['jpg','jpeg','png']))
                            <img src="{{ $url }}" alt="{{ $document->document_type }}" class="img-fluid rounded">
                        @elseif(in_array(strtolower($extension), ['mp4','mov']))
                            <video controls class="w-100 rounded">
                                <source src="{{ $url }}" type="video/{{ strtolower($extension) }}">
                                Your browser does not support video playback.
                            </video>
                        @else
                            <a href="{{ $url }}" target="_blank" class="btn ph-btn-outline">Open Document</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="d-flex gap-2">
    @if(!$user->is_verified)
        <form method="POST" action="{{ route('admin.users.verify', $user) }}">
            @csrf
            <button class="btn btn-success">Verify User</button>
        </form>
    @endif
    <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
        @csrf
        <button class="btn btn-outline-warning">{{ $user->is_active ? 'Suspend' : 'Activate' }}</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-bs-toggle-tooltip="tooltip"]').forEach(function (element) {
        new bootstrap.Tooltip(element);
    });
</script>
@endpush
