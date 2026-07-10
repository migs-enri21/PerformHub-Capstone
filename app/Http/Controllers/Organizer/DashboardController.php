<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PerformerRecommendationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(PerformerRecommendationService $recommendations): View
    {
        $profile = Auth::user()->organizerProfile;
        $pendingBookings = Booking::where('organizer_id', Auth::id())->where('status', 'pending')->count();
        $activeBookings = Booking::where('organizer_id', Auth::id())
            ->where('status', 'accepted')
            ->count();
        $recommendedPerformers = $recommendations->forOrganizer(Auth::user());

        return view('organizer.dashboard', compact('profile', 'pendingBookings', 'activeBookings', 'recommendedPerformers'));
    }
}
