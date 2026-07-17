<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
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
            return redirect()->route('organizer.profile.show')->withFragment('calendar')->with('error', 'Google Calendar is not configured on this server yet.');
        }

        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        return redirect()->away($googleCalendar->authorizationUrl($state));
    }

    public function callback(Request $request, OrganizerGoogleCalendarService $googleCalendar): RedirectResponse
    {
        if ($request->get('state') !== session('google_oauth_state')) {
            return redirect()
                ->route('organizer.profile.show')
                ->withFragment('calendar')
                ->with('error', 'Google Calendar connection failed. Please try again.');
        }

        session()->forget('google_oauth_state');

        if ($request->filled('error')) {
            return redirect()
                ->route('organizer.profile.show')
                ->withFragment('calendar')
                ->with('error', 'Google Calendar connection was cancelled.');
        }

        $profile = Auth::user()->organizerProfile()->firstOrFail();

        try {
            $tokens = $googleCalendar->exchangeAuthorizationCode((string) $request->get('code'));
            $googleCalendar->connect($profile, $tokens);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('organizer.profile.show')
                ->withFragment('calendar')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('organizer.profile.show')
            ->withFragment('calendar')
            ->with('success', 'Google Calendar connected and synced.');
    }

    public function sync(OrganizerGoogleCalendarService $googleCalendar): RedirectResponse
    {
        $profile = Auth::user()->organizerProfile()->firstOrFail();

        if (! $profile->google_calendar_connected) {
            return redirect()
                ->route('organizer.profile.show')
                ->withFragment('calendar')
                ->with('error', 'Connect Google Calendar first.');
        }

        try {
            $googleCalendar->syncBusyDates($profile);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('organizer.profile.show')
                ->withFragment('calendar')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('organizer.profile.show')
            ->withFragment('calendar')
            ->with('success', 'Google Calendar synced.');
    }

    public function disconnect(OrganizerGoogleCalendarService $googleCalendar): RedirectResponse
    {
        $profile = Auth::user()->organizerProfile()->firstOrFail();
        $googleCalendar->disconnect($profile);

        return redirect()
            ->route('organizer.profile.show')
            ->withFragment('calendar')
            ->with('success', 'Google Calendar disconnected.');
    }
}
