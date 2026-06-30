@extends('onboarding.layout', ['title' => 'All Set', 'current' => 4])

@section('onboarding-content')
<div class="text-center mb-4">
    <div class="onboarding-success-icon mb-3">
        <i class="fas fa-check"></i>
    </div>
    <h2 class="fw-bold mb-2">You're all set!</h2>
    <p class="text-muted">
        Your <strong>{{ $user->role }}</strong> account has been created.
        @if($user->isOrganizer())
            Your verification documents are under review and you'll be notified within 24–48 hours.
        @else
            Your identity verification is under review and you'll be notified within 24–48 hours.
        @endif
    </p>
</div>

<div class="ph-card p-4 mb-4">
    @php
        $checklist = [
            ['label' => 'Account Created', 'done' => true],
            ['label' => 'Profile Completed', 'done' => true],
            ['label' => 'Documents Submitted', 'done' => true],
            ['label' => 'Identity Verification', 'done' => false, 'pending' => true],
            ['label' => 'Verified Badge', 'done' => false, 'pending' => true],
        ];
    @endphp
    @foreach($checklist as $item)
        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: var(--ph-border) !important;">
            <span class="small">{{ $item['label'] }}</span>
            @if($item['done'] ?? false)
                <span class="badge rounded-pill bg-success">Done</span>
            @else
                <span class="badge rounded-pill bg-warning text-dark">Pending</span>
            @endif
        </div>
    @endforeach
</div>

<a href="{{ $user->dashboardRoute() }}" class="btn ph-btn-primary w-100 mb-2">
    Go to Dashboard <i class="fas fa-arrow-right ms-2"></i>
</a>
<p class="text-center text-muted small mb-0">
    You can still use the platform while your verification is being processed.
</p>
@endsection
