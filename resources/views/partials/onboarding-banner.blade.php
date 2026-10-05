@php
    $user = auth()->user();
    $isFullyVerified = $user->isPerformer()
        ? $user->isPerformerVerified()
        : (bool) $user->is_verified;
    $awaitingVerification = ! $user->isAdmin() && ! $isFullyVerified;
@endphp

@if($awaitingVerification)
<div class="ph-card p-4 mb-4 onboarding-banner">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div class="d-flex align-items-start gap-3">
            <div class="onboarding-banner-icon flex-shrink-0">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                <h6 class="fw-semibold mb-1">Limited access — waiting for admin verification</h6>
                <p class="text-muted small mb-0">
                    Your government ID is under review. An admin will verify your account within 24–48 hours. You can edit your profile and portfolio while you wait.
                </p>
            </div>
        </div>
    </div>
</div>
@endif
