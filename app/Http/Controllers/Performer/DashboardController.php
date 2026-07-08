<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $profile = $user->performerProfile;
        $pendingBookings = Booking::where('performer_id', $user->id)->where('status', 'pending')->count();
        $upcomingBookings = Booking::where('performer_id', $user->id)
            ->whereIn('status', ['accepted', 'interview_scheduled'])
            ->count();
        $reviews = Review::where('reviewee_id', $user->id)->with('reviewer')->latest()->limit(5)->get();

        return view('performer.dashboard', compact('profile', 'pendingBookings', 'upcomingBookings', 'reviews'));
    }
}
