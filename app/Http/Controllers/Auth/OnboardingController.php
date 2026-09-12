<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\HandlesVerificationDocuments;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Support\PhilippineLocations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    use HandlesVerificationDocuments;

    public function index(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->hasCompletedOnboarding()) {
            return redirect($user->dashboardRoute());
        }

        return redirect($user->onboardingRoute());
    }

    public function showProfile(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasCompletedOnboarding() && $user->is_verified) {
            return redirect($user->dashboardRoute());
        }

        // Role is chosen at registration now; legacy accounts still sitting at the
        // old "registered" step are bumped straight to profile instead of being
        // shown a role-selection screen.
        if ($user->onboarding_step < User::ONBOARDING_PROFILE) {
            $user->update(['onboarding_step' => User::ONBOARDING_PROFILE]);
        }

        return view('onboarding.profile', ['user' => $user]);
    }

    public function storeProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate(array_merge([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
        ], PhilippineLocations::locationFieldsRules()));

        $locationData = PhilippineLocations::profileLocationAttributes($validated);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
        ]);

        if ($user->isPerformer()) {
            $user->performerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge([
                    'stage_name' => $user->fullName(),
                ], $locationData)
            );

            $user->update(['onboarding_step' => User::ONBOARDING_VERIFICATION]);

            return redirect()->route('onboarding.verification');
        }

        $user->organizerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge([
                'organization_name' => $user->fullName(),
                'phone' => $validated['phone'],
            ], $locationData)
        );

        $user->update(['onboarding_step' => User::ONBOARDING_VERIFICATION]);

        return redirect()->route('onboarding.verification');
    }

    public function showVerification(): View|RedirectResponse
    {
        $user = Auth::user()->load('verificationDocuments');

        if ($user->hasCompletedOnboarding()) {
            return redirect($user->dashboardRoute());
        }

        if ($user->onboarding_step < User::ONBOARDING_VERIFICATION) {
            return redirect($user->onboardingRoute());
        }

        if ($user->onboarding_step > User::ONBOARDING_VERIFICATION) {
            return redirect()->route('onboarding.complete');
        }

        $hasGovernmentId = $user->verificationDocuments->contains('document_type', 'government_id');

        return view('onboarding.verification', ['user' => $user, 'hasGovernmentId' => $hasGovernmentId]);
    }

    public function storeVerification(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $hasGovernmentId = $user->verificationDocuments()->where('document_type', 'government_id')->exists();
        $governmentIdRule = $hasGovernmentId ? ['nullable', 'array', 'max:5'] : ['required', 'array', 'min:1', 'max:5'];
        $governmentIdFileRule = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];

        if ($user->isOrganizer()) {
            $validated = $request->validate([
                'organization_type' => ['required', 'in:company,individual,nonprofit'],
                'government_id' => $governmentIdRule,
                'government_id.*' => $governmentIdFileRule,
                'business_permit' => ['required_unless:organization_type,individual', 'array', 'min:1', 'max:5'],
                'business_permit.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
                'proof_of_events' => ['nullable', 'array', 'max:5'],
                'proof_of_events.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,zip', 'max:51200'],
                'bir_certificate' => ['nullable', 'array', 'max:5'],
                'bir_certificate.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ]);

            $user->organizerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                ['organization_type' => $validated['organization_type']]
            );

            if ($request->hasFile('government_id')) {
                foreach ($request->file('government_id', []) as $file) {
                    $this->storeVerificationDocument($user, 'government_id', $file, [], false);
                }
            }
            foreach ($request->file('business_permit', []) as $index => $file) {
                $this->storeVerificationDocument($user, 'business_permit', $file, [], $index === 0);
            }

            if ($request->hasFile('proof_of_events')) {
                foreach ($request->file('proof_of_events', []) as $index => $file) {
                    $this->storeVerificationDocument($user, 'proof_of_events', $file, [], $index === 0);
                }
            }

            if ($request->hasFile('bir_certificate')) {
                foreach ($request->file('bir_certificate', []) as $index => $file) {
                    $this->storeVerificationDocument($user, 'bir_certificate', $file, [], $index === 0);
                }
            }
        } else {
            $request->validate([
                'government_id' => $governmentIdRule,
                'government_id.*' => $governmentIdFileRule,
                'proof_of_events' => ['nullable', 'array', 'max:5'],
                'proof_of_events.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,zip', 'max:51200'],
            ]);

            if ($request->hasFile('government_id')) {
                foreach ($request->file('government_id', []) as $file) {
                    $this->storeVerificationDocument($user, 'government_id', $file, [], false);
                }
            }

            foreach ($request->file('proof_of_events', []) as $index => $file) {
                $this->storeVerificationDocument($user, 'proof_of_events', $file, [], $index === 0);
            }
        }

        return $this->finishOnboarding($user);
    }

    private function finishOnboarding(User $user): RedirectResponse
    {
        $roleType = $user->isPerformer() ? 'Performer' : 'Organizer';

        foreach (User::where('role', User::ROLE_ADMIN)->get() as $admin) {
            Notification::send(
                $admin,
                'new_registration',
                "New {$roleType} ready for verification",
                "{$user->fullName()} completed sign-up and needs verification.",
                route('admin.users.show', $user)
            );
        }

        $user->update(['onboarding_step' => User::ONBOARDING_COMPLETE]);

        return redirect()->route('onboarding.complete');
    }

    public function showComplete(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->onboarding_step < User::ONBOARDING_COMPLETE) {
            return redirect($user->onboardingRoute());
        }

        return view('onboarding.complete', ['user' => $user]);
    }

    public function dismissBanner(Request $request): RedirectResponse
    {
        $request->session()->put('onboarding_banner_dismissed', true);

        return back();
    }
}
