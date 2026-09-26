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
        $userCounts = User::query()
            ->selectRaw('COUNT(*) as users')
            ->selectRaw('COUNT(CASE WHEN role = ? THEN 1 END) as performers', [User::ROLE_PERFORMER])
            ->selectRaw('COUNT(CASE WHEN role = ? THEN 1 END) as organizers', [User::ROLE_ORGANIZER])
            ->selectRaw(
                'COUNT(CASE WHEN is_verified = ? AND role IN (?, ?) THEN 1 END) as pending_verifications',
                [false, User::ROLE_PERFORMER, User::ROLE_ORGANIZER]
            )
            ->first();

        $stats = [
            'users' => (int) $userCounts->users,
            'performers' => (int) $userCounts->performers,
            'organizers' => (int) $userCounts->organizers,
            'bookings' => Booking::count(),
            'pending_verifications' => (int) $userCounts->pending_verifications,
            'unread_notifications' => Auth::user()->notifications()->where('is_read', false)->count(),
        ];

        $recentBookings = Booking::with(['organizer', 'performer'])->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }
}
