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
    <div class="row g-4">
        <div class="col-md-6">
            <h5 class="fw-bold">Basic Details</h5>
            <dl class="row">
                <dt class="col-sm-4 text-muted">Name</dt>
                <dd class="col-sm-8">{{ $user->fullName() }}</dd>

                <dt class="col-sm-4 text-muted">Username</dt>
                <dd class="col-sm-8">{{ $user->username }}</dd>

                <dt class="col-sm-4 text-muted">Email</dt>
                <dd class="col-sm-8">{{ $user->email }}</dd>

                <dt class="col-sm-4 text-muted">Role</dt>
                <dd class="col-sm-8">{{ ucfirst($user->role) }}</dd>

                <dt class="col-sm-4 text-muted">Status</dt>
                <dd class="col-sm-8">{{ $user->is_active ? 'Active' : 'Suspended' }}</dd>

                <dt class="col-sm-4 text-muted">Verified</dt>
                <dd class="col-sm-8">{{ $user->is_verified ? 'Yes' : 'No' }}</dd>

                <dt class="col-sm-4 text-muted">Onboarding</dt>
                <dd class="col-sm-8">{{ $user->onboardingStepLabel() }}</dd>
            </dl>
        </div>

        <div class="col-md-6">
            <h5 class="fw-bold">Profile Info</h5>
            @if($user->isPerformer() && $user->performerProfile)
                <dl class="row">
                    <dt class="col-sm-4 text-muted">Stage Name</dt>
                    <dd class="col-sm-8">{{ $user->performerProfile->stage_name }}</dd>

                    <dt class="col-sm-4 text-muted">Genre</dt>
                    <dd class="col-sm-8">{{ $user->performerProfile->genre ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted">Location</dt>
                    <dd class="col-sm-8">{{ $user->performerProfile->shortLocation() }}</dd>
                </dl>
            @elseif($user->isOrganizer() && $user->organizerProfile)
                <dl class="row">
                    <dt class="col-sm-4 text-muted">Organization</dt>
                    <dd class="col-sm-8">{{ $user->organizerProfile->organization_name }}</dd>

                    <dt class="col-sm-4 text-muted">Type</dt>
                    <dd class="col-sm-8">{{ ucfirst($user->organizerProfile->organization_type ?? 'N/A') }}</dd>

                    <dt class="col-sm-4 text-muted">Location</dt>
                    <dd class="col-sm-8">{{ $user->organizerProfile->shortLocation() }}</dd>
                </dl>
            @endif
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
