<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PhilippineLocations;
use App\Support\PhoneNumbers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use RuntimeException;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();

                return back()->withErrors(['email' => 'Your account has been deactivated.'])->onlyInput('email');
            }

            $this->clearRegistrationDraft($request);

            $intendedUrl = $request->session()->get('url.intended');
            $intendedPath = is_string($intendedUrl) ? parse_url($intendedUrl, PHP_URL_PATH) : null;
            $onboardingPath = parse_url(route('onboarding.index'), PHP_URL_PATH);

            if (
                is_string($intendedPath)
                && is_string($onboardingPath)
                && ($intendedPath === $onboardingPath || str_starts_with($intendedPath, rtrim($onboardingPath, '/').'/'))
            ) {
                $request->session()->forget('url.intended');
            }

            return redirect()->intended($user->dashboardRoute());
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function showResetPassword(string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $status = PasswordBroker::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => null,
                ])->save();
            }
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
        }

        return redirect()->route('login')->with('status', __($status));
    }

    public function showRegister(Request $request): View
    {
        $role = $request->query('role', 'performer');

        return view('auth.register', compact('role'));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'first_name' => ['required', 'string', 'max:100', "regex:/^[a-zA-Z\\s\\-']+$/"],
            'last_name' => ['required', 'string', 'max:100', "regex:/^[a-zA-Z\\s\\-']+$/"],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => PhoneNumbers::rules(required: true),
            'role' => ['required', 'in:performer,organizer'],
            'terms_accepted' => ['accepted'],
            'government_id' => ['required', 'array', 'min:1', 'max:5'],
            'government_id.*' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], PhilippineLocations::locationFieldsRules()), [
            'email.unique' => 'An account with this email already exists.',
            'password.confirmed' => 'Password and confirm password do not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'phone.regex' => PhoneNumbers::message(),
            'terms_accepted.accepted' => 'You must agree to the Terms & Agreement before continuing.',
            'first_name.regex' => 'First name may only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.regex' => 'Last name may only contain letters, spaces, hyphens, and apostrophes.',
            'government_id.required' => 'Please upload at least one valid government ID file.',
        ]);

        $this->clearRegistrationDraft($request);
        $draftId = (string) Str::uuid();
        $governmentIds = [];

        foreach ($request->file('government_id') as $file) {
            $path = $file->store("registration-drafts/{$draftId}", 'local');

            if (! is_string($path)) {
                Storage::disk('local')->deleteDirectory("registration-drafts/{$draftId}");
                throw new RuntimeException('Unable to temporarily store a government ID upload.');
            }

            $governmentIds[] = [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
            ];
        }

        $request->session()->put('registration_draft', [
            'id' => $draftId,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'region' => $validated['region'],
            'city' => $validated['city'],
            'barangay' => $validated['barangay'] ?? null,
            'location' => PhilippineLocations::profileLocationAttributes($validated)['location'],
            'government_ids' => $governmentIds,
            'profile_completed' => true,
        ]);

        return redirect()->route('onboarding.verification');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->clearRegistrationDraft($request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function cancelRegistration(Request $request): RedirectResponse
    {
        $this->clearRegistrationDraft($request);

        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function clearRegistrationDraft(Request $request): void
    {
        $draft = $request->session()->get('registration_draft', []);

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
