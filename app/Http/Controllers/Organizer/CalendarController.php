<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Services\OrganizerGoogleCalendarService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(OrganizerGoogleCalendarService $googleCalendar): View
    {
        $profile = $this->getProfile();
        $wasGoogleConnected = $profile->google_calendar_connected;

        if ($googleCalendar->shouldSync($profile)) {
            try {
                $googleCalendar->syncBusyDates($profile);
                $profile->refresh();
            } catch (\Throwable $exception) {
                $message = $exception->getMessage();

                if (str_contains($message, 'link expired') || str_contains($message, 'link is invalid')) {
                    session()->flash('error', $message);
                    $profile->refresh();
                }
            }
        } elseif ($wasGoogleConnected && ! $profile->google_calendar_connected) {
            session()->flash('error', 'Your Google Calendar link is invalid. Click Connect Google Calendar to sign in again.');
        }

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
                'start_time' => $this->shortTime($event->start_time),
                'end_time' => $this->shortTime($event->end_time),
                'venue' => $event->venue,
                'status' => $event->status,
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
                'start_time' => $this->shortTime($busyDate->start_time),
                'end_time' => $this->shortTime($busyDate->end_time),
            ];
        }

        return $googleBusy;
    }

    private function shortTime(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        return substr($time, 0, 5);
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
