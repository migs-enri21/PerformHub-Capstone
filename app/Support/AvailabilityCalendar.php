<?php

namespace App\Support;

use App\Models\PerformerProfile;
use Illuminate\Support\Collection;

class AvailabilityCalendar
{
    /** @return array<string, array{summary: ?string}> */
    public static function googleBusyMap(PerformerProfile $profile): array
    {
        if (! $profile->google_calendar_connected) {
            return [];
        }

        return $profile->googleCalendarBusyDates
            ->mapWithKeys(fn ($busyDate) => [
                $busyDate->date->format('Y-m-d') => [
                    'summary' => $busyDate->summary,
                ],
            ])
            ->all();
    }

    public static function loadCalendarRelations(PerformerProfile $profile): PerformerProfile
    {
        return $profile->load([
            'availabilitySchedules' => fn ($query) => $query->orderBy('date'),
            'bookings' => fn ($query) => $query
                ->whereIn('status', ['pending', 'interview_scheduled', 'accepted', 'completed'])
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
