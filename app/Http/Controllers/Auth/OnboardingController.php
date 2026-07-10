<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\OrganizerProfile;
use App\Models\PerformerProfile;
use App\Models\User;
use App\Models\VerificationDocument;
use App\Support\PhilippineLocations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\SupabaseStorageService;

class OnboardingController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isAdmin() || $user->hasCompletedOnboarding()) {
            return redirect($user->dashboardRoute());
        }

        return redirect($user->onboardingRoute());
    }

    public function showRole(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasCompletedOnboarding()) {
            return redirect($user->dashboardRoute());
        }

        if ($user->onboarding_step > User::ONBOARDING_REGISTERED) {
            return redirect($user->onboardingRoute());
        }

        return view('onboarding.role', ['user' => $user]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'role' => ['required', 'in:performer,organizer'],
        ]);

        if ($user->role !== $validated['role']) {
            $user->update(['role' => $validated['role']]);

            if ($user->isPerformer()) {
                $user->organizerProfile?->delete();
                PerformerProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    ['stage_name' => $user->fullName()]
                );
            } else {
                $user->performerProfile?->delete();
                OrganizerProfile::firstOrCreate(
                    ['user_id' => $user->id],
                    ['organization_name' => $user->fullName()]
                );
            }
        }

        $user->update(['onboarding_step' => User::ONBOARDING_PROFILE]);

        return redirect()->route('onboarding.profile');
    }

    public function showProfile(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasCompletedOnboarding()) {
            return redirect($user->dashboardRoute());
        }

        if ($user->onboarding_step < User::ONBOARDING_PROFILE) {
            return redirect()->route('onboarding.role');
        }

        if ($user->onboarding_step > User::ONBOARDING_PROFILE) {
            return redirect($user->onboardingRoute());
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
            'onboarding_step' => User::ONBOARDING_VERIFICATION,
        ]);

        if ($user->isPerformer()) {
            $user->performerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge([
                    'stage_name' => $user->fullName(),
                ], $locationData)
            );
        } else {
            $user->organizerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge([
                    'organization_name' => $user->fullName(),
                    'phone' => $validated['phone'],
                ], $locationData)
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

        return view('onboarding.verification', ['user' => $user]);
    }

    public function storeVerification(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isOrganizer()) {
            $validated = $request->validate([
                'organization_type' => ['required', 'in:company,individual,nonprofit'],
                'government_id' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
                'business_permit' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
                'proof_of_events' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,zip', 'max:51200'],
                'bir_certificate' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ]);

            $user->organizerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                ['organization_type' => $validated['organization_type']]
            );

            $this->storeDocument($user, 'government_id', $request->file('government_id'));
            $this->storeDocument($user, 'business_permit', $request->file('business_permit'));

            if ($request->hasFile('proof_of_events')) {
                $this->storeDocument($user, 'proof_of_events', $request->file('proof_of_events'));
            }

            if ($request->hasFile('bir_certificate')) {
                $this->storeDocument($user, 'bir_certificate', $request->file('bir_certificate'));
            }
        } else {
            $validated = $request->validate([
                'government_id' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
                'performance_sample' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,mp4,mov', 'max:51200'],
            ]);

            $this->storeDocument($user, 'government_id', $request->file('government_id'));

            if ($request->hasFile('performance_sample')) {
                $this->storeDocument($user, 'performance_sample', $request->file('performance_sample'));
            }
        }

        // Notify admins about new registration
        $roleType = $user->isPerformer() ? 'Performer' : 'Organizer';
        $adminUsers = User::where('role', 'admin')->get(); // Get all admins
        
        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'new_registration',
                'title' => "New $roleType Registered",
                'message' => "A new $roleType has registered: {$user->fullName()}",
                'link' => route('admin.users.index'),
                'is_read' => false,
            ]);
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

    private function storeDocument(User $user, string $type, \Illuminate\Http\UploadedFile $file): void
{
    $existing = $user->verificationDocuments()->where('document_type', $type)->first();

    if ($existing) {$existing->delete();}

    $supabase = new SupabaseStorageService();

    $bucket = $user->isPerformer()? 'performer-files': 'organizer-files';

    $path = $supabase->upload($file,$bucket,$type,$user->id);

    VerificationDocument::create([
        'user_id' => $user->id,
        'document_type' => $type,
        'file_path' => $path,
        'original_name' => $file->getClientOriginalName(),
    ]);
    }
}
