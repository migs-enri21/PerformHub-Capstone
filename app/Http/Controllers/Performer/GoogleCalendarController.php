<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GoogleCalendarController extends Controller
{
    public function connect(GoogleCalendarService $googleCalendar): RedirectResponse
    {
        if (! $googleCalendar->isConfigured()) {
            return redirect()
                ->route('performer.profile.show')
                ->withFragment('availability')
                ->with('error', 'Google Calendar is not configured on this server yet.');
        }

        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        return redirect()->away($googleCalendar->authorizationUrl($state));
    }

    public function callback(Request $request, GoogleCalendarService $googleCalendar): RedirectResponse
    {
        if ($request->get('state') !== session('google_oauth_state')) {
            return redirect()
                ->route('performer.profile.show')
                ->withFragment('availability')
                ->with('error', 'Google Calendar connection failed. Please try again.');
        }

        session()->forget('google_oauth_state');

        if ($request->filled('error')) {
            return redirect()
                ->route('performer.profile.show')
                ->withFragment('availability')
                ->with('error', 'Google Calendar connection was cancelled.');
        }

        $profile = Auth::user()->performerProfile()->firstOrFail();

        try {
            $tokens = $googleCalendar->exchangeAuthorizationCode((string) $request->get('code'));
            $googleCalendar->connect($profile, $tokens);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('performer.profile.show')
                ->withFragment('availability')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('performer.profile.show')
            ->withFragment('availability')
            ->with('success', 'Google Calendar connected and synced.');
    }

    public function sync(GoogleCalendarService $googleCalendar): RedirectResponse
    {
        $profile = Auth::user()->performerProfile()->firstOrFail();

        if (! $profile->google_calendar_connected) {
            return redirect()
                ->route('performer.profile.show')
                ->withFragment('availability')
                ->with('error', 'Connect Google Calendar first.');
        }

        try {
            $googleCalendar->syncBusyDates($profile);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('performer.profile.show')
                ->withFragment('availability')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('performer.profile.show')
            ->withFragment('availability')
            ->with('success', 'Google Calendar synced.');
    }

    public function disconnect(GoogleCalendarService $googleCalendar): RedirectResponse
    {
        $profile = Auth::user()->performerProfile()->firstOrFail();
        $googleCalendar->disconnect($profile);

        return redirect()
            ->route('performer.profile.show')
            ->withFragment('availability')
            ->with('success', 'Google Calendar disconnected.');
    }
}
