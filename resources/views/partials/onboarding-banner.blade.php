@if(auth()->user()->hasLimitedAccess() && !session('onboarding_banner_dismissed'))
<div class="ph-card p-4 mb-4 onboarding-banner">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div class="d-flex align-items-start gap-3">
            <div class="onboarding-banner-icon flex-shrink-0">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                <h6 class="fw-semibold mb-1">Limited access — complete sign-up to unlock everything</h6>
                <p class="text-muted small mb-0">
                    @if(auth()->user()->isPerformer())
                        You can explore your dashboard now. Finish your profile and verification to accept bookings, upload portfolio, and set availability.
                    @else
                        You can explore your dashboard and browse performers. Finish your profile and verification to send booking requests.
                    @endif
                </p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-shrink-0">
            <form method="POST" action="{{ route('onboarding.dismiss-banner') }}">
                @csrf
                <button type="submit" class="btn ph-btn-outline btn-sm">Maybe later</button>
            </form>
            <a href="{{ auth()->user()->onboardingRoute() }}" class="btn ph-btn-primary btn-sm">
                Complete sign-up <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
@endif
