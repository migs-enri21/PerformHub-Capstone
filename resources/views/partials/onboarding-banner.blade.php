@if(auth()->user()->hasLimitedAccess())
<div class="ph-card p-4 mb-4 onboarding-banner">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div class="d-flex align-items-start gap-3">
            <div class="onboarding-banner-icon flex-shrink-0">
                <i class="fas fa-lock"></i>
            </div>
            <div>
                <h6 class="fw-semibold mb-1">Limited access — your account needs admin verification</h6>
                <p class="text-muted small mb-0">
                    Your account is pending verification by an administrator. Other features remain restricted until your account is approved.
                </p>
            </div>
        </div>
    </div>
</div>
@endif
