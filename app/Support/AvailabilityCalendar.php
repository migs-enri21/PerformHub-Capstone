<?php

namespace App\Support;

use App\Models\PerformerProfile;
use Illuminate\Support\Collection;

class AvailabilityCalendar
{
    /** @return array<string, array{summary: ?string, start_time: ?string, end_time: ?string}> */
    public static function googleBusyMap(PerformerProfile $profile): array
    {
        if (! $profile->google_calendar_connected) {
            return [];
        }

        return $profile->googleCalendarBusyDates
            ->mapWithKeys(function ($busyDate) {
                $start = $busyDate->start_time
                    ? \Illuminate\Support\Str::substr((string) $busyDate->start_time, 0, 5)
                    : null;
                $end = $busyDate->end_time
                    ? \Illuminate\Support\Str::substr((string) $busyDate->end_time, 0, 5)
                    : null;

                return [
                    $busyDate->date->format('Y-m-d') => [
                        'summary' => $busyDate->summary,
                        'start_time' => $start,
                        'end_time' => $end,
                    ],
                ];
            })
            ->all();
    }

    public static function loadCalendarRelations(PerformerProfile $profile): PerformerProfile
    {
        return $profile->load([
            'availabilitySchedules' => fn ($query) => $query->orderBy('date'),
            'bookings' => fn ($query) => $query
                ->whereIn('status', ['pending', 'accepted', 'completed'])
                ->orderBy('event_date'),
            'googleCalendarBusyDates' => fn ($query) => $query->orderBy('date'),
        ]);
    }

    public static function calendarData(PerformerProfile $profile): array
    {
        return [
            'schedules' => $profile->availabilitySchedules,
            'bookingCalendar' => $profile->bookings,
            'googleBusy' => self::googleBusyMap($profile),
        ];
    }
}
