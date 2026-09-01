@php
    $user = auth()->user();
    $needsOnboarding = $user->hasLimitedAccess();
    $isFullyVerified = $user->isPerformer()
        ? (bool) $user->performerProfile?->is_verified_badge
        : (bool) $user->is_verified;
    $awaitingVerification = ! $user->isAdmin() && ! $needsOnboarding && ! $isFullyVerified;
@endphp

@if($needsOnboarding || $awaitingVerification)
<div class="ph-card p-4 mb-4 onboarding-banner">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div class="d-flex align-items-start gap-3">
            <div class="onboarding-banner-icon flex-shrink-0">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                @if($needsOnboarding)
                    <h6 class="fw-semibold mb-1">Limited access — complete sign-up to unlock everything</h6>
                    <p class="text-muted small mb-0">
                        @if($user->isPerformer())
                            You can explore your dashboard now. Finish your profile and verification to accept bookings, upload portfolio, and set availability.
                        @else
                            You can explore your dashboard and browse performers. Finish your profile and verification to send booking requests.
                        @endif
                    </p>
                @else
                    <h6 class="fw-semibold mb-1">Limited access — waiting for admin verification</h6>
                    <p class="text-muted small mb-0">
                        Your government ID is under review. An admin will verify your account within 24–48 hours. You can still explore your dashboard while you wait.
                    </p>
                @endif
            </div>
        </div>
        @if($needsOnboarding)
            <div class="d-flex gap-2 flex-shrink-0">
                <a href="{{ $user->onboardingRoute() }}" class="btn ph-btn-primary btn-sm">
                    Complete sign-up <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        @endif
    </div>
</div>
@endif
