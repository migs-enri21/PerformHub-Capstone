<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Services\OrganizerGoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleCalendarController extends Controller
{
    public function connect(OrganizerGoogleCalendarService $googleCalendar): RedirectResponse
    {
        if (! $googleCalendar->isConfigured()) {
            return $this->calendarMessage('error', 'Google Calendar is not configured on this server yet.');
        }

        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        return redirect()->away($googleCalendar->authorizationUrl($state));
    }

    public function callback(Request $request, OrganizerGoogleCalendarService $googleCalendar): RedirectResponse
    {
        if ($request->get('state') !== session('google_oauth_state')) {
            return $this->calendarMessage('error', 'Google Calendar connection failed. Please try again.');
        }

        session()->forget('google_oauth_state');

        if ($request->filled('error')) {
            return $this->calendarMessage('error', 'Google Calendar connection was cancelled.');
        }

        $profile = $this->getProfile();

        try {
            $tokens = $googleCalendar->exchangeAuthorizationCode((string) $request->get('code'));
            $googleCalendar->connect($profile, $tokens);
        } catch (\Throwable $exception) {
            return $this->calendarMessage('error', $exception->getMessage());
        }

        return $this->calendarMessage('success', 'Google Calendar connected and synced.');
    }

    public function sync(OrganizerGoogleCalendarService $googleCalendar): RedirectResponse
    {
        $profile = $this->getProfile();

        if (! $profile->google_calendar_connected) {
            return $this->calendarMessage('error', 'Connect Google Calendar first.');
        }

        try {
            $googleCalendar->syncBusyDates($profile);
        } catch (\Throwable $exception) {
            return $this->calendarMessage('error', $exception->getMessage());
        }

        return $this->calendarMessage('success', 'Google Calendar synced.');
    }

    public function disconnect(OrganizerGoogleCalendarService $googleCalendar): RedirectResponse
    {
        $profile = $this->getProfile();
        $googleCalendar->disconnect($profile);

        return $this->calendarMessage('success', 'Google Calendar disconnected.');
    }

    private function getProfile(): OrganizerProfile
    {
        return Auth::user()->organizerProfile()->firstOrFail();
    }

    private function calendarMessage(string $type, string $message): RedirectResponse
    {
        return redirect()
            ->route('organizer.calendar.index')
            ->with($type, $message);
    }
}
