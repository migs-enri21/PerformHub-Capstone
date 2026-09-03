<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Event;
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

        $events = Event::with('eventType')
            ->where('organizer_id', $organizer->id)
            ->whereIn('status', ['Open', 'open'])
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        return view('performer.organizers.show', compact('profile', 'events'));
    }
}
