<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AvailabilityCalendar;
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

        $usersQuery = $query->with(['performerProfile', 'organizerProfile']);

        switch ($request->query('sort', 'latest')) {
            case 'name_asc':
                $usersQuery
                    ->orderByRaw('LOWER(first_name)')
                    ->orderByRaw('LOWER(last_name)');
                break;
            case 'name_desc':
                $usersQuery
                    ->orderByRaw('LOWER(first_name) DESC')
                    ->orderByRaw('LOWER(last_name) DESC');
                break;
            default:
                $usersQuery->latest('created_at');
        }

        $users = $usersQuery->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        abort_unless(in_array($user->role, ['performer', 'organizer']), 404);

        $user->load(['performerProfile.categories', 'organizerProfile', 'verificationDocuments']);

        return view('admin.users.show', compact('user'));
    }

    public function showPerformerProfile(User $user): View
    {
        abort_unless($user->isPerformer() && $user->performerProfile, 404);

        $performer = AvailabilityCalendar::loadCalendarRelations(
            $user->performerProfile->load(['user', 'categories', 'portfolios'])
        );

        $calendar = AvailabilityCalendar::calendarData($performer);

        return view('admin.users.performer-profile', compact('user', 'performer', 'calendar'));
    }

    public function verify(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['performer', 'organizer']), 400);

        $user->loadMissing(['performerProfile.categories', 'organizerProfile']);

        if ($user->isPerformer()) {
            if (! $user->profile_checked_by_admin && ! $user->markProfileDetailsComplete()) {
                return back()->with('warning', 'Complete Profile Details before admin verification.');
            }
        }

        $user->update([
            'is_verified' => true,
            'onboarding_step' => User::ONBOARDING_COMPLETE,
        ]);

        if ($user->isPerformer() && $user->performerProfile) {
            $user->performerProfile->update(['is_verified_badge' => true]);
        }

        return back()->with('success', 'Account verified successfully.');
    }

    public function checkProfile(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['performer', 'organizer']), 400);

        $user->loadMissing(['performerProfile.categories', 'organizerProfile']);

        $user->update(['profile_checked_by_admin' => true]);

        return back()->with('success', 'Profile manually checked. Admin verification is now available.');
    }

    public function uncheckProfile(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['performer', 'organizer']), 400);

        $user->update([
            'profile_checked_by_admin' => false,
            'onboarding_step' => User::ONBOARDING_PROFILE,
        ]);

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
