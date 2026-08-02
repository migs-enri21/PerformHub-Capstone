<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        $profile = Auth::user()->organizerProfile;

        $events = Event::where('organizer_id', Auth::id())
            ->orderBy('event_date')
            ->get();

        $googleBusyDates = $profile->googleCalendarBusyDates()
            ->orderBy('date')
            ->get();

        $calendarEvents = [];

        foreach ($events as $event) {
            $date = (string) $event->event_date;

            if (! isset($calendarEvents[$date])) {
                $calendarEvents[$date] = [
                    'title' => $event->title,
                    'url' => route('organizer.events.show', $event),
                ];
            }
        }

        $googleBusy = [];

        foreach ($googleBusyDates as $busyDate) {
            $googleBusy[$busyDate->date->format('Y-m-d')] = ['summary' => $busyDate->summary,];
        }

        $upcomingEvents = [];
        $today = now()->toDateString();

        foreach ($events as $event) {
            if ($event->event_date >= $today) {
                $upcomingEvents[] = $event;
            }

            if (count($upcomingEvents) === 4) {
                break;
            }
        }

        return view('organizer.calendar.index', compact('profile','events','calendarEvents','googleBusy','upcomingEvents',));
    }
}
