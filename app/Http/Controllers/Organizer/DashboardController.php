<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PerformerRecommendationService;
use App\Support\PortfolioFeed;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(PerformerRecommendationService $recommendations): View
    {
        $profile = Auth::user()->organizerProfile;
        $pendingBookings = Booking::where('organizer_id', Auth::id())->where('status', 'pending')->count();
        $activeBookings = Booking::where('organizer_id', Auth::id())
            ->whereIn('status', ['accepted', 'interview_scheduled'])
            ->count();
        $recommendedPerformers = $recommendations->forOrganizer(Auth::user());
        $feedPosts = PortfolioFeed::recentPosts(12);

        return view('organizer.dashboard', compact('profile', 'pendingBookings', 'activeBookings', 'recommendedPerformers', 'feedPosts'));
    }
}
