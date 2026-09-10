<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\HandlesVerificationDocuments;
use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\PerformerProfile;
use App\Models\User;
use App\Models\Notification;
use App\Support\PhilippineLocations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    use HandlesVerificationDocuments;

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

            return redirect()->intended($user->dashboardRoute());
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function showRegister(Request $request): View
    {
        $role = $request->query('role', 'performer');

        return view('auth.register', compact('role'));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => ['required', 'string', 'max:30'],
            'role' => ['required', 'in:performer,organizer'],
            'terms_accepted' => ['accepted'],
            'government_id' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], PhilippineLocations::locationFieldsRules()), [
            'email.unique' => 'An account with this email already exists.',
            'terms_accepted.accepted' => 'You must agree to the Terms & Agreement before continuing.',
            'password.confirmed' => 'Password and confirm password do not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'government_id.required' => 'Please upload a valid government ID.',
        ]);

        $locationData = PhilippineLocations::profileLocationAttributes($validated);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'is_verified' => false,
            'is_active' => true,
            'onboarding_step' => $validated['role'] === 'performer'
                ? User::ONBOARDING_COMPLETE
                : User::ONBOARDING_VERIFICATION,
        ]);

        if ($user->isPerformer()) {
            PerformerProfile::create(array_merge([
                'user_id' => $user->id,
                'stage_name' => $user->fullName(),
            ], $locationData));
        } else {
            OrganizerProfile::create(array_merge([
                'user_id' => $user->id,
                'organization_name' => $user->fullName(),
                'phone' => $validated['phone'],
            ], $locationData));
        }

        $this->storeVerificationDocument($user, 'government_id', $request->file('government_id'));

        Auth::login($user);

        $admins = User::where('role', User::ROLE_ADMIN)->get();
        $type = 'user.registered';
        $title = $user->isOrganizer() ? 'New Organizer Registered' : 'New Performer Registered';
        $message = sprintf('%s (%s) just created an account.', $user->fullName(), $user->email);
        $link = route('admin.users.show', $user);

        foreach ($admins as $admin) {
            Notification::send($admin, $type, $title, $message, $link);
        }

        if ($user->isOrganizer()) {
            return redirect()->route('onboarding.verification')
                ->with('success', 'Account created. Upload your organization documents to finish sign-up.');
        }

        return redirect($user->dashboardRoute())
            ->with('success', 'Welcome to PerformHub! Your identity is under review.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
