<?php

namespace App\Http\Controllers;

use App\Models\PerformerProfile;
use App\Models\Review;
use App\Support\PortfolioFeed;
use Illuminate\View\View;

class TalentProfileController extends Controller
{
    public function show(PerformerProfile $performer): View
    {
        $performer->load(['user', 'category', 'portfolios', 'availabilitySchedules', 'bookings']);
        $reviews = Review::where('reviewee_id', $performer->user_id)->with('reviewer')->latest()->get();

        $portfolioGroups = PortfolioFeed::groupItems($performer->portfolios);

        return view('talent.show', compact('performer', 'reviews', 'portfolioGroups'));
    }
}
