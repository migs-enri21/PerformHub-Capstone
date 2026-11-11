<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\HandlesVerificationDocuments;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\OrganizerProfile;
use App\Models\PerformerProfile;
use App\Models\User;
use App\Services\SupabaseStorageService;
use App\Support\PhilippineLocations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class OnboardingController extends Controller
{
    use HandlesVerificationDocuments;

    public function index(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            if ($user->isAdmin() || $user->hasCompletedOnboarding()) {
                return redirect($user->dashboardRoute());
            }

            return redirect($user->onboardingRoute());
        }

        if ($this->registrationDraft($request)) {
            return redirect()->route('onboarding.profile');
        }

        return redirect()->route('register');
    }

    public function showProfile(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            if ($user->isAdmin()) {
                return redirect($user->dashboardRoute());
            }

            if ($user->onboarding_step < User::ONBOARDING_PROFILE) {
                $user->update(['onboarding_step' => User::ONBOARDING_PROFILE]);
            }

            return view('onboarding.profile', ['user' => $user]);
        }

        $draft = $this->registrationDraft($request);

        if (! $draft) {
            return redirect()->route('register');
        }

        return view('onboarding.profile', ['user' => $this->draftUser($draft)]);
    }

    public function storeProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $draft = $user ? null : $this->registrationDraft($request);

        if (! $user && ! $draft) {
            return redirect()->route('register');
        }

        $validated = $request->validate(array_merge([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
        ], PhilippineLocations::locationFieldsRules()));

        $locationData = PhilippineLocations::profileLocationAttributes($validated);

        if (! $user) {
            $draft = array_merge($draft, [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'region' => $locationData['region'],
                'city' => $locationData['city'],
                'barangay' => $locationData['barangay'],
                'location' => $locationData['location'],
                'profile_completed' => true,
            ]);
            $request->session()->put('registration_draft', $draft);

            return redirect()->route('onboarding.verification');
        }

        $onboardingComplete = $user->hasCompletedOnboarding();

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
        ]);

        if ($user->isPerformer()) {
            $user->performerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge($onboardingComplete ? [] : ['stage_name' => $user->fullName()], $locationData)
            );
        } else {
            $user->organizerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge(
                    $onboardingComplete ? [] : ['organization_name' => $user->fullName()],
                    ['phone' => $validated['phone']],
                    $locationData
                )
            );
        }

        if (! $onboardingComplete) {
            $user->update(['onboarding_step' => User::ONBOARDING_VERIFICATION]);
        }

        return redirect()->route('onboarding.verification');
    }

    public function showVerification(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            $draft = $this->registrationDraft($request);

            if (! $draft || ! ($draft['profile_completed'] ?? false)) {
                return redirect()->route($draft ? 'onboarding.profile' : 'register');
            }

            return view('onboarding.verification', [
                'user' => $this->draftUser($draft),
                'hasGovernmentId' => ! empty($draft['government_ids']),
            ]);
        }

        $user->load('verificationDocuments');

        if (! $user->hasCompletedOnboarding() && $user->onboarding_step < User::ONBOARDING_VERIFICATION) {
            return redirect($user->onboardingRoute());
        }

        if (! $user->hasCompletedOnboarding() && $user->onboarding_step > User::ONBOARDING_VERIFICATION) {
            return redirect()->route('onboarding.complete');
        }

        $hasGovernmentId = $user->verificationDocuments->contains('document_type', 'government_id');

        return view('onboarding.verification', compact('user', 'hasGovernmentId'));
    }

    public function storeVerification(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $draft = $user ? null : $this->registrationDraft($request);

        if (! $user && ! $draft) {
            return redirect()->route('register');
        }

        if (! $user && ! ($draft['profile_completed'] ?? false)) {
            return redirect()->route('onboarding.profile')
                ->with('warning', 'Complete your profile details before verification.');
        }

        if ($user && $user->onboarding_step < User::ONBOARDING_VERIFICATION) {
            return redirect()->route('onboarding.profile')
                ->with('warning', 'Complete your profile details before verification.');
        }

        $user ??= $this->draftUser($draft);
        $hasGovernmentId = $draft
            ? ! empty($draft['government_ids'])
            : $user->verificationDocuments()->where('document_type', 'government_id')->exists();
        $governmentIdRule = $hasGovernmentId
            ? ['nullable', 'array', 'max:'.max(0, 5 - count($draft['government_ids'] ?? []))]
            : ['required', 'array', 'min:1', 'max:5'];
        $governmentIdFileRule = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];

        if ($user->isOrganizer()) {
            $request->validate([
                'organization_type' => ['required', 'in:freelancer,agency'],
                'government_id' => $governmentIdRule,
                'government_id.*' => $governmentIdFileRule,
                'business_permit' => ['required', 'array', 'min:1', 'max:5'],
                'business_permit.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
                'proof_of_employment' => ['required_if:organization_type,agency', 'array', 'min:1', 'max:5'],
                'proof_of_employment.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
                'proof_of_events' => ['nullable', 'array', 'max:5'],
                'proof_of_events.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,zip', 'max:51200'],
            ]);

            if ($draft) {
                $draft['organization_type'] = $request->input('organization_type');
                $request->session()->put('registration_draft', $draft);
            } else {
                $user->organizerProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['organization_type' => $request->input('organization_type')]
                );
            }

            if (! $draft) {
                foreach ($request->file('government_id', []) as $file) {
                    $this->storeVerificationDocument($user, 'government_id', $file, [], false);
                }

                foreach (['business_permit', 'proof_of_employment', 'proof_of_events'] as $type) {
                    foreach ($request->file($type, []) as $index => $file) {
                        $this->storeVerificationDocument($user, $type, $file, [], $index === 0);
                    }
                }
            }
        } else {
            $request->validate([
                'government_id' => $governmentIdRule,
                'government_id.*' => $governmentIdFileRule,
                'proof_of_events' => ['nullable', 'array', 'max:5'],
                'proof_of_events.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,zip', 'max:51200'],
            ]);

            if (! $draft) {
                foreach ($request->file('government_id', []) as $file) {
                    $this->storeVerificationDocument($user, 'government_id', $file, [], false);
                }

                foreach ($request->file('proof_of_events', []) as $index => $file) {
                    $this->storeVerificationDocument($user, 'proof_of_events', $file, [], $index === 0);
                }
            }
        }

        if ($draft) {
            return $this->createAccountFromDraft($request, $draft);
        }

        return $this->finishOnboarding($user);
    }

    private function finishOnboarding(User $user): RedirectResponse
    {
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

    private function registrationDraft(Request $request): ?array
    {
        $draft = $request->session()->get('registration_draft');

        return is_array($draft) ? $draft : null;
    }

    private function draftUser(array $draft): User
    {
        $user = new User([
            'first_name' => $draft['first_name'],
            'last_name' => $draft['last_name'],
            'email' => $draft['email'],
            'phone' => $draft['phone'],
            'role' => $draft['role'],
        ]);

        $location = [
            'region' => $draft['region'] ?? null,
            'city' => $draft['city'] ?? null,
            'barangay' => $draft['barangay'] ?? null,
            'location' => $draft['location'] ?? null,
        ];

        if ($user->isPerformer()) {
            $user->setRelation('performerProfile', new PerformerProfile(array_merge([
                'stage_name' => $user->fullName(),
            ], $location)));
        } else {
            $user->setRelation('organizerProfile', new OrganizerProfile(array_merge([
                'organization_name' => $user->fullName(),
                'phone' => $user->phone,
                'organization_type' => $draft['organization_type'] ?? null,
            ], $location)));
        }

        return $user;
    }

    private function createAccountFromDraft(Request $request, array $draft): RedirectResponse
    {
        if (User::where('email', $draft['email'])->exists()) {
            $this->clearRegistrationDraft($request);

            return redirect()->route('login')
                ->withErrors(['email' => 'An account with this email already exists. Please sign in.'])
                ->withInput(['email' => $draft['email']]);
        }

        $uploadedPaths = [];
        $bucket = $draft['role'] === User::ROLE_PERFORMER ? 'performer-files' : 'organizer-files';

        try {
            $user = DB::transaction(function () use ($request, $draft, &$uploadedPaths): User {
                $user = User::create([
                    'first_name' => $draft['first_name'],
                    'last_name' => $draft['last_name'],
                    'email' => $draft['email'],
                    'password' => $draft['password'],
                    'phone' => $draft['phone'],
                    'role' => $draft['role'],
                    'is_verified' => false,
                    'is_active' => true,
                    'onboarding_step' => User::ONBOARDING_COMPLETE,
                ]);

                $location = [
                    'region' => $draft['region'],
                    'city' => $draft['city'],
                    'barangay' => $draft['barangay'],
                    'location' => $draft['location'],
                ];

                if ($user->isPerformer()) {
                    $user->performerProfile()->create(array_merge([
                        'stage_name' => $user->fullName(),
                    ], $location));
                } else {
                    $user->organizerProfile()->create(array_merge([
                        'organization_name' => $user->fullName(),
                        'organization_type' => $draft['organization_type'],
                        'phone' => $user->phone,
                    ], $location));
                }

                foreach ($draft['government_ids'] as $file) {
                    $uploadedPaths[] = $this->storeVerificationDocument($user, 'government_id', $this->draftUpload($file), [], false);
                }

                foreach ($request->file('government_id', []) as $file) {
                    $uploadedPaths[] = $this->storeVerificationDocument($user, 'government_id', $file, [], false);
                }

                $additionalDocuments = $user->isOrganizer()
                    ? ['business_permit', 'proof_of_employment', 'proof_of_events']
                    : ['proof_of_events'];

                foreach ($additionalDocuments as $type) {
                    foreach ($request->file($type, []) as $index => $file) {
                        $uploadedPaths[] = $this->storeVerificationDocument($user, $type, $file, [], $index === 0);
                    }
                }

                $title = $user->isOrganizer() ? 'New Organizer Registered' : 'New Performer Registered';
                $message = sprintf('%s (%s) just created an account.', $user->fullName(), $user->email);

                foreach (User::where('role', User::ROLE_ADMIN)->get() as $admin) {
                    Notification::send($admin, 'user.registered', $title, $message, route('admin.users.show', $user));
                }

                return $user;
            });
        } catch (Throwable $exception) {
            $storage = new SupabaseStorageService;

            foreach ($uploadedPaths as $path) {
                $storage->delete($bucket, $path);
            }

            throw $exception;
        }

        $this->clearRegistrationDraft($request);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('onboarding.complete')
            ->with('success', 'Your account has been created and submitted for verification.');
    }

    private function draftUpload(array $file): UploadedFile
    {
        $path = Storage::disk('local')->path($file['path']);

        if (! is_file($path)) {
            throw new RuntimeException('A staged registration upload is missing. Please restart registration.');
        }

        return new UploadedFile(
            $path,
            $file['original_name'],
            $file['mime_type'],
            null,
            true
        );
    }

    private function clearRegistrationDraft(Request $request): void
    {
        $draft = $this->registrationDraft($request) ?? [];

        foreach ($draft['government_ids'] ?? [] as $file) {
            if (isset($file['path']) && str_starts_with($file['path'], 'registration-drafts/')) {
                Storage::disk('local')->delete($file['path']);
            }
        }

        if (isset($draft['id']) && is_string($draft['id'])) {
            Storage::disk('local')->deleteDirectory('registration-drafts/'.$draft['id']);
        }

        $request->session()->forget('registration_draft');
    }
}
