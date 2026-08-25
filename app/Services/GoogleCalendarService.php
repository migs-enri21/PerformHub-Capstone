<?php

namespace App\Services;

use App\Models\GoogleCalendarBusyDate;
use App\Models\PerformerProfile;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleCalendarService
{
    private const CALENDAR_READONLY_SCOPE = 'https://www.googleapis.com/auth/calendar.readonly';

    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect_uri'));
    }

    public function authorizationUrl(string $state): string
    {
        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect_uri'),
            'response_type' => 'code',
            'scope' => self::CALENDAR_READONLY_SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.$query;
    }

    public function exchangeAuthorizationCode(string $code): array
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect_uri'),
            'grant_type' => 'authorization_code',
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Unable to connect Google Calendar.');
        }

        return $response->json();
    }

    public function shouldSync(PerformerProfile $profile): bool
    {
        if (! $profile->google_calendar_connected || blank($this->refreshToken($profile))) {
            return false;
        }

        if ($profile->google_calendar_synced_at === null) {
            return true;
        }

        return $profile->google_calendar_synced_at->lte(now()->subMinutes(15));
    }

    public function connect(PerformerProfile $profile, array $tokenPayload): void
    {
        if (empty($tokenPayload['refresh_token'])) {
            throw new RuntimeException('Google did not return a refresh token. Disconnect the app in your Google Account and try again.');
        }

        $profile->update([
            'google_calendar_connected' => true,
            'google_calendar_id' => 'primary',
            'google_refresh_token' => $tokenPayload['refresh_token'],
            'google_token_expires_at' => isset($tokenPayload['expires_in'])
                ? now()->addSeconds((int) $tokenPayload['expires_in'])
                : null,
        ]);

        $this->syncBusyDates($profile->fresh());
    }

    public function disconnect(PerformerProfile $profile): void
    {
        $profile->googleCalendarBusyDates()->delete();

        PerformerProfile::whereKey($profile->id)->update([
            'google_calendar_connected' => false,
            'google_calendar_id' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
            'google_calendar_synced_at' => null,
        ]);

        $profile->refresh();
    }

    public function syncBusyDates(PerformerProfile $profile): void
    {
        if (! $profile->google_calendar_connected || blank($this->refreshToken($profile))) {
            return;
        }

        $accessToken = $this->accessToken($profile);
        $calendarId = $profile->google_calendar_id ?: 'primary';
        $timeMin = now()->subDays(30)->startOfDay()->toIso8601String();
        $timeMax = now()->addDays(90)->endOfDay()->toIso8601String();

        $response = Http::withToken($accessToken)->get(
            'https://www.googleapis.com/calendar/v3/calendars/'.rawurlencode($calendarId).'/events',
            [
                'timeMin' => $timeMin,
                'timeMax' => $timeMax,
                'singleEvents' => 'true',
                'orderBy' => 'startTime',
                'maxResults' => 250,
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException('Unable to sync Google Calendar events.');
        }

        $busyDates = [];

        foreach ($response->json('items', []) as $event) {
            if (($event['status'] ?? null) === 'cancelled') {
                continue;
            }

            $dateKey = $this->eventDateKey($event);

            if ($dateKey === null) {
                continue;
            }

            $summary = Str::limit((string) ($event['summary'] ?? 'Busy'), 255, '');
            [$startTime, $endTime] = $this->eventTimes($event);

            if (! isset($busyDates[$dateKey])) {
                $busyDates[$dateKey] = [
                    'summary' => $summary,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ];

                continue;
            }

            if ($busyDates[$dateKey]['summary'] !== $summary && $summary !== 'Busy') {
                $busyDates[$dateKey]['summary'] = Str::limit(
                    $busyDates[$dateKey]['summary'].', '.$summary,
                    255,
                    ''
                );
            }

            // If several events land on one day, keep the widest timed window.
            if ($startTime && (
                $busyDates[$dateKey]['start_time'] === null
                || $startTime < $busyDates[$dateKey]['start_time']
            )) {
                $busyDates[$dateKey]['start_time'] = $startTime;
            }

            if ($endTime && (
                $busyDates[$dateKey]['end_time'] === null
                || $endTime > $busyDates[$dateKey]['end_time']
            )) {
                $busyDates[$dateKey]['end_time'] = $endTime;
            }
        }

        $profile->googleCalendarBusyDates()->delete();

        foreach ($busyDates as $date => $busyDate) {
            GoogleCalendarBusyDate::create([
                'performer_profile_id' => $profile->id,
                'date' => $date,
                'summary' => $busyDate['summary'],
                'start_time' => $busyDate['start_time'],
                'end_time' => $busyDate['end_time'],
            ]);
        }

        $profile->update([
            'google_calendar_synced_at' => now(),
        ]);
    }

    private function accessToken(PerformerProfile $profile): string
    {
        return $this->refreshAccessToken($profile);
    }

    private function refreshAccessToken(PerformerProfile $profile): string
    {
        $refreshToken = $this->refreshToken($profile);

        if (blank($refreshToken)) {
            throw new RuntimeException('Your Google Calendar link is invalid. Click Connect Google Calendar to sign in again.');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        if ($response->failed()) {
            $error = (string) $response->json('error', '');

            if (in_array($error, ['invalid_grant', 'invalid_token', 'unauthorized_client'], true)) {
                $this->disconnect($profile);

                throw new RuntimeException('Your Google Calendar link expired. Click Connect Google Calendar to sign in again.');
            }

            throw new RuntimeException('Unable to refresh Google Calendar access. Please try again or reconnect.');
        }

        $payload = $response->json();

        if (empty($payload['access_token'])) {
            throw new RuntimeException('Google Calendar did not return a valid access token.');
        }

        $profile->update([
            'google_token_expires_at' => isset($payload['expires_in'])
                ? now()->addSeconds((int) $payload['expires_in'])
                : null,
        ]);

        return (string) $payload['access_token'];
    }

    private function eventDateKey(array $event): ?string
    {
        if (! empty($event['start']['date'])) {
            return $event['start']['date'];
        }

        if (! empty($event['start']['dateTime'])) {
            return Carbon::parse($event['start']['dateTime'])
                ->timezone(config('app.timezone'))
                ->toDateString();
        }

        return null;
    }

    /**
     * @return array{0: ?string, 1: ?string} H:i times, or nulls for all-day events
     */
    private function eventTimes(array $event): array
    {
        // All-day Google events use start.date / end.date with no clock times.
        if (! empty($event['start']['date']) || empty($event['start']['dateTime'])) {
            return [null, null];
        }

        $tz = config('app.timezone');

        $start = Carbon::parse($event['start']['dateTime'])->timezone($tz)->format('H:i');
        $end = ! empty($event['end']['dateTime'])
            ? Carbon::parse($event['end']['dateTime'])->timezone($tz)->format('H:i')
            : null;

        return [$start, $end];
    }

    private function refreshToken(PerformerProfile $profile): ?string
    {
        if (! $profile->google_calendar_connected) {
            return null;
        }

        try {
            return filled($profile->google_refresh_token) ? $profile->google_refresh_token : null;
        } catch (DecryptException) {
            $this->disconnect($profile);

            return null;
        }
    }
}
