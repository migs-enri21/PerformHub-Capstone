<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\HandlesVerificationDocuments;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Models\VerificationDocument;
use App\Services\SupabaseStorageService;
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

        if ($user->hasCompletedOnboarding()) {
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
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location' => ['nullable', 'string', 'max:500'],
        ]));

        $user->update(['onboarding_step' => User::ONBOARDING_VERIFICATION]);

        if ($user->isPerformer()) {
            $user->performerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge([
                    'stage_name' => $user->fullName(),
                ], collect($validated)->only(['latitude', 'longitude', 'location'])->all())
            );
        } else {
            $user->organizerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge([
                    'organization_name' => $user->fullName(),
                    'phone' => $user->phone,
                ], collect($validated)->only(['latitude', 'longitude', 'location'])->all())
            );
        }

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
        $governmentIdRule = $hasGovernmentId ? ['nullable', 'array'] : ['required', 'array', 'min:1'];
        $governmentIdFileRule = ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];

        if ($user->isOrganizer()) {
            $validated = $request->validate([
                'organization_type' => ['required', 'in:company,individual,nonprofit'],
                'government_id' => array_merge($governmentIdRule, ['max:5']),
                'government_id.*' => $governmentIdFileRule,
                'business_permit' => ['required_unless:organization_type,individual', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
                'proof_of_events' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,zip', 'max:51200'],
                'bir_certificate' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ]);

            $user->organizerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                ['organization_type' => $validated['organization_type']]
            );

            if ($request->hasFile('government_id')) {
                foreach ($request->file('government_id', []) as $file) {
                    $this->storeVerificationDocument($user, 'government_id', $file);
                }
            }
            if ($request->hasFile('business_permit')) {
                $this->storeVerificationDocument($user, 'business_permit', $request->file('business_permit'));
            }

            if ($request->hasFile('proof_of_events')) {
                $this->storeVerificationDocument($user, 'proof_of_events', $request->file('proof_of_events'));
            }

            if ($request->hasFile('bir_certificate')) {
                $this->storeVerificationDocument($user, 'bir_certificate', $request->file('bir_certificate'));
            }
        } else {
            $validated = $request->validate([
                'government_id' => array_merge($governmentIdRule, ['max:5']),
                'government_id.*' => $governmentIdFileRule,
            ]);

            if ($request->hasFile('government_id')) {
                foreach ($request->file('government_id', []) as $file) {
                    $this->storeVerificationDocument($user, 'government_id', $file);
                }
            }
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

    private function storeDocument(User $user, string $type, \Illuminate\Http\UploadedFile $file, array $meta = [], bool $replaceExisting = true): void
    {
        $existing = $replaceExisting
            ? $user->verificationDocuments()->where('document_type', $type)->get()
            : collect();

        foreach ($existing as $document) {
            $document->delete();
        }

        $supabase = new SupabaseStorageService();

        $bucket = $user->isPerformer() ? 'performer-files' : 'organizer-files';

        $path = $supabase->upload($file, $bucket, $type, $user->id);

        VerificationDocument::create([
            'user_id' => $user->id,
            'document_type' => $type,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'government_id_type' => $meta['government_id_type'] ?? null,
            'government_id_other' => $meta['government_id_other'] ?? null,
        ]);
    }
}
