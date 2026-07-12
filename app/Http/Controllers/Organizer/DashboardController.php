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
        $profile = Auth::user()->organizerProfile;

        $myEvents = Event::where('organizer_id', Auth::id())->latest()->take(5)->get();

        $pendingBookings = Booking::where('organizer_id', Auth::id())->where('status', 'pending')->count();

        $activeBookings = Booking::where('organizer_id', Auth::id())
            ->whereIn('status', ['accepted', 'interview_scheduled'])
            ->count();

        $recommendedPerformers = $recommendations->forOrganizer(Auth::user());

        return view('organizer.dashboard', compact('profile', 'myEvents', 'pendingBookings', 'activeBookings', 'recommendedPerformers'));
    }
}
