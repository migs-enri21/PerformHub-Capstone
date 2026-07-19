<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventApplication;
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
            ->where('status', 'accepted')
            ->count();
        $reviews = Review::where('reviewee_id', $user->id)->with('reviewer')->latest()->limit(5)->get();

        $availableEvents = Event::with(['organizer.organizerProfile', 'eventType', 'preferredCategory', 'photos'])
            ->whereRaw('LOWER(status) = ?', ['open'])
            ->whereDate('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        foreach ($availableEvents as $event) {
            if ($event->cover_photo && $event->photos->isEmpty()) {
                $photo = $event->photos()->create([
                    'file_path' => $event->cover_photo,
                    'sort_order' => 0,
                ]);
                $event->setRelation('photos', collect([$photo]));
            }
        }

        $appliedEventIds = EventApplication::where('performer_id', $user->id)
            ->pluck('event_id')
            ->all();

        return view('performer.dashboard', compact('profile', 'pendingBookings', 'upcomingBookings', 'reviews', 'availableEvents', 'appliedEventIds'));
    }
    public function clickMe(): View
    {
        return view('performer.click-me');
    }
}
