<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'performers' => User::where('role', 'performer')->count(),
            'organizers' => User::where('role', 'organizer')->count(),
            'bookings' => Booking::count(),
            'pending_verifications' => User::where('is_verified', false)
                ->whereIn('role', ['performer', 'organizer'])
                ->count(),
            'unread_notifications' => Auth::user()->notifications()->where('is_read', false)->count(),
        ];

        $recentBookings = Booking::with(['organizer', 'performer'])->latest()->limit(5)->get();

        $recentRegistrationAlerts = Auth::user()
            ->notifications()
            ->whereIn('type', ['user.registered', 'new_registration'])
            ->latest()
            ->limit(5)
            ->get();

        $organizersForFilter = User::where('role', 'organizer')->orderBy('first_name')->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentBookings',
            'recentRegistrationAlerts',
            'organizersForFilter'
        ));
    }
}
