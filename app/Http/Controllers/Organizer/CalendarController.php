<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizerProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        $profile = $this->getProfile();
        $events = $this->getEvents();
        $googleBusyDates = $profile->googleCalendarBusyDates()->orderBy('date')->get();

        return view('organizer.calendar.index', [
            'profile' => $profile,
            'events' => $events,
            'calendarEvents' => $this->getCalendarEvents($events),
            'googleBusy' => $this->getGoogleBusyDates($googleBusyDates),
            'upcomingEvents' => $this->getUpcomingEvents($events),
        ]);
    }

    private function getProfile(): OrganizerProfile
    {
        return Auth::user()->organizerProfile()->firstOrFail();
    }

    private function getEvents(): Collection
    {
        return Event::where('organizer_id', Auth::id())
            ->orderBy('event_date')
            ->get();
    }

    private function getCalendarEvents(Collection $events): array
    {
        $calendarEvents = [];

        foreach ($events as $event) {
            $date = (string) $event->event_date;

            $calendarEvents[$date][] = [
                'title' => $event->title,
                'url' => route('organizer.events.show', $event),
            ];
        }

        return $calendarEvents;
    }

    private function getGoogleBusyDates(Collection $busyDates): array
    {
        $googleBusy = [];

        foreach ($busyDates as $busyDate) {
            $googleBusy[$busyDate->date->format('Y-m-d')] = [
                'summary' => $busyDate->summary,
            ];
        }

        return $googleBusy;
    }

    private function getUpcomingEvents(Collection $events): array
    {
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

        return $upcomingEvents;
    }
}
