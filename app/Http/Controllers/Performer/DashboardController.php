<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventApplication;
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

        $categoryIds = [];

        if ($profile) {
            $categoryIds = $profile->categories()->pluck('categories.id')->all();
        }

        $availableEvents = $this->getRecommendedEvents($categoryIds);

        foreach ($availableEvents as $event) {
            if ($event->cover_photo && $event->photos->isEmpty()) {
                $photo = $event->photos()->create([
                    'file_path' => $event->cover_photo,
                    'sort_order' => 0,
                ]);
                $event->setRelation('photos', collect([$photo]));
            }
        }

        $applicationStatuses = EventApplication::where('performer_id', $user->id)
            ->pluck('status', 'event_id')
            ->all();

        $pendingBookingUrls = Booking::where('performer_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('event_id')
            ->get()
            ->mapWithKeys(fn (Booking $booking) => [
                $booking->event_id => route('performer.bookings.show', $booking),
            ])
            ->all();

        return view('performer.dashboard', compact(
            'profile',
            'pendingBookings',
            'upcomingBookings',
            'availableEvents',
            'applicationStatuses',
            'pendingBookingUrls',
        ));
    }
    public function clickMe(): View
    {
        return view('performer.click-me');
    }

    private function getRecommendedEvents(array $categoryIds)
    {
        return Event::with(['organizer.organizerProfile', 'eventType', 'categories', 'photos'])
            ->whereIn('status', ['Open', 'open'])
            ->whereDate('event_date', '>=', now()->toDateString())
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();
    }
}
