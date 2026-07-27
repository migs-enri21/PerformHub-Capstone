<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PerformerRecommendationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Event;

class DashboardController extends Controller
{
    public function index(PerformerRecommendationService $recommendations): View
    {
        $upcomingEvents = Event::where('organizer_id', Auth::id())
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->take(3)
            ->get();

        $pendingBookings = Booking::where('organizer_id', Auth::id())->where('status', 'pending')->count();

        $activeBookings = Booking::where('organizer_id', Auth::id())
            ->where('status', 'accepted')
            ->count();

        $recentNotifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(3)
            ->get();

        $recommendedPerformers = $recommendations->forOrganizer(Auth::user());

        return view('organizer.dashboard', compact(
            'upcomingEvents',
            'pendingBookings',
            'activeBookings',
            'recentNotifications',
            'recommendedPerformers',
        ));
    }
}
