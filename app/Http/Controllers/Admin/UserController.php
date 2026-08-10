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

        $users = $query
            ->with(['performerProfile', 'organizerProfile'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        abort_unless(in_array($user->role, ['performer', 'organizer']), 404);

        $user->load(['performerProfile', 'organizerProfile', 'verificationDocuments']);

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
