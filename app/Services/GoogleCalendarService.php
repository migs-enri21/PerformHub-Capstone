<?php

namespace App\Services;

use App\Models\GoogleCalendarBusyDate;
use App\Models\PerformerProfile;
use Carbon\Carbon;
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
        if (! $profile->google_calendar_connected || blank($profile->google_refresh_token)) {
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

        $profile->update([
            'google_calendar_connected' => false,
            'google_calendar_id' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
            'google_calendar_synced_at' => null,
        ]);
    }

    public function syncBusyDates(PerformerProfile $profile): void
    {
        if (! $profile->google_calendar_connected || blank($profile->google_refresh_token)) {
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

            if (! isset($busyDates[$dateKey])) {
                $busyDates[$dateKey] = $summary;

                continue;
            }

            if ($busyDates[$dateKey] !== $summary && $summary !== 'Busy') {
                $busyDates[$dateKey] = Str::limit($busyDates[$dateKey].', '.$summary, 255, '');
            }
        }

        $profile->googleCalendarBusyDates()->delete();

        foreach ($busyDates as $date => $summary) {
            GoogleCalendarBusyDate::create([
                'performer_profile_id' => $profile->id,
                'date' => $date,
                'summary' => $summary,
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
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $profile->google_refresh_token,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Google Calendar authorization expired. Please reconnect.');
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
            return Carbon::parse($event['start']['dateTime'])->toDateString();
        }

        return null;
    }
}
