<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\PerformerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        $role = $request->query('role', 'organizer');

        return view('auth.login', compact('role'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required', 'in:performer,organizer,admin'],
        ]);

        $role = $credentials['role'];
        unset($credentials['role']);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();

                return back()->withErrors(['email' => 'Your account has been deactivated.'])->onlyInput('email', 'role');
            }

            if ($user->role !== $role) {
                Auth::logout();

                return back()->withErrors(['email' => 'Invalid credentials for the selected role.'])->onlyInput('email', 'role');
            }

            return redirect()->intended($user->dashboardRoute());
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email', 'role');
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
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'in:performer,organizer'],
        ], [
            'username.alpha_dash' => 'Username can only use letters, numbers, dashes, and underscores (spaces are converted automatically).',
            'username.unique' => 'That username is already taken. Try another one.',
            'email.unique' => 'An account with this email already exists.',
            'password.confirmed' => 'Password and confirm password do not match.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_verified' => false,
            'is_active' => true,
            'onboarding_step' => User::ONBOARDING_REGISTERED,
        ]);

        if ($user->isPerformer()) {
            PerformerProfile::create([
                'user_id' => $user->id,
                'stage_name' => $user->fullName(),
            ]);
        } else {
            OrganizerProfile::create([
                'user_id' => $user->id,
                'organization_name' => $user->fullName(),
            ]);
        }

        Auth::login($user);

        return redirect($user->dashboardRoute())
            ->with('success', 'Welcome to PerformHub! Your account is ready — complete sign-up anytime to unlock all features.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
