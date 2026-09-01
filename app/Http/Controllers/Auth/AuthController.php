<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\HandlesVerificationDocuments;
use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\PerformerProfile;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
        $username = Str::slug(trim((string) $request->input('username', '')), '_');

        if ($username === '') {
            $username = Str::slug(
                trim($request->input('first_name', '').' '.$request->input('last_name', '')),
                '_'
            );
        }

        $request->merge(['username' => $username]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location' => ['nullable', 'string', 'max:500'],
            'role' => ['required', 'in:performer,organizer'],
            'terms_accepted' => ['accepted'],
            'government_id' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [
            'username.alpha_dash' => 'Username can only use letters, numbers, dashes, and underscores (spaces are converted automatically).',
            'username.unique' => 'That username is already taken. Try another one.',
            'email.unique' => 'An account with this email already exists.',
            'password.confirmed' => 'Password and confirm password do not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'government_id.required' => 'Please upload a valid government ID.',
        ]);

        $locationData = [
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'location' => $validated['location'] ?? null,
        ];

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
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
