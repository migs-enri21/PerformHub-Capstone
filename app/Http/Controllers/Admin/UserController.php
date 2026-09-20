<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->whereIn('role', ['performer', 'organizer']);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->verification === 'pending') {
            $query->where('is_verified', false);
        }

        $users = $query
            ->with(['performerProfile', 'organizerProfile'])
            ->latest('created_at')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        abort_unless(in_array($user->role, ['performer', 'organizer']), 404);

        $user->load(['performerProfile.categories', 'organizerProfile', 'verificationDocuments']);

        return view('admin.users.show', compact('user'));
    }

    public function verify(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['performer', 'organizer']), 400);

        $user->update(['is_verified' => true]);

        if ($user->isPerformer() && $user->performerProfile) {
            $user->performerProfile->update(['is_verified_badge' => true]);
        }

        return back()->with('success', 'Account verified successfully.');
    }

    public function checkProfile(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['performer', 'organizer']), 400);

        $profile = $user->isPerformer() ? $user->performerProfile()->with('categories')->first() : $user->organizerProfile;
        $hasLocation = filled($profile?->region) && filled($profile?->city) && filled($profile?->barangay);
        $profileIsComplete = filled($user->first_name)
            && filled($user->last_name)
            && filled($user->phone)
            && $hasLocation
            && ($user->isPerformer()
                ? filled($profile?->stage_name) && $profile->categories->isNotEmpty()
                : filled($profile?->organization_name) && filled($profile?->organization_type));

        if (! $profileIsComplete) {
            return back()->with('warning', 'Profile details are incomplete. The user must finish the required profile fields first.');
        }

        $user->update(['onboarding_step' => User::ONBOARDING_VERIFICATION]);

        return back()->with('success', 'Profile checked successfully. Profile Details is now complete.');
    }

    public function uncheckProfile(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['performer', 'organizer']), 400);

        $user->update(['onboarding_step' => User::ONBOARDING_PROFILE]);

        return back()->with('success', 'Profile check removed. The user must complete Profile Details again.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        abort_unless($user->role !== 'admin', 400);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Account status updated.');
    }

    /**
     * Display a simple list of all users with their role (name + role only).
     */
    public function all(): View
    {
        $users = User::orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.users.all', compact('users'));
    }
}
