<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Interview;
use App\Models\User;
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
            'interviews' => Interview::count(),
        ];

        $recentBookings = Booking::with(['organizer', 'performer'])->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }
}
