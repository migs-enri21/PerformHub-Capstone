<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\User;
use Illuminate\View\View;

class OrganizerProfileController extends Controller
{
    public function show(User $organizer): View
    {
        if (! $organizer->isOrganizer()) {
            abort(404);
        }

        $profile = $organizer->organizerProfile;

        if (! $profile) {
            abort(404);
        }

        $events = Event::with(['organizer.organizerProfile', 'eventType', 'categories', 'photos'])
            ->where('organizer_id', $organizer->id)
            ->orderByDesc('event_date')
            ->orderByDesc('start_time')
            ->get();

        $applicationStatuses = EventApplication::where('performer_id', auth()->id())
            ->whereIn('event_id', $events->pluck('id'))
            ->pluck('status', 'event_id');

        $pendingBookingUrls = Booking::where('performer_id', auth()->id())
            ->where('status', 'pending')
            ->whereIn('event_id', $events->pluck('id'))
            ->get()
            ->mapWithKeys(function (Booking $booking) {
                return [
                    $booking->event_id => route('performer.bookings.show', $booking),
                ];
            });

        return view('performer.organizers.show', compact(
            'profile',
            'events',
            'applicationStatuses',
            'pendingBookingUrls'
        ));
    }
}
