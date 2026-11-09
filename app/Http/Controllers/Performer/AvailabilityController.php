<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\AvailabilitySchedule;
use App\Models\Booking;
use App\Models\EventApplication;
use App\Models\Notification;
use App\Models\PerformerProfile;
use App\Services\GoogleCalendarService;
use App\Support\AvailabilityCalendar;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function index(GoogleCalendarService $googleCalendar): View
    {
        $profile = Auth::user()->performerProfile()->with('categories')->firstOrFail();
        $profile = AvailabilityCalendar::loadCalendarRelations($profile);
        $this->clearStaleBookingBlocks($profile);
        $profile = AvailabilityCalendar::loadCalendarRelations($profile->fresh('categories'));

        $wasGoogleConnected = $profile->google_calendar_connected;

        if ($googleCalendar->shouldSync($profile)) {
            try {
                $googleCalendar->syncBusyDates($profile);
                $profile = AvailabilityCalendar::loadCalendarRelations($profile->fresh('categories'));
            } catch (\Throwable $exception) {
                if (str_contains($exception->getMessage(), 'link expired')
                    || str_contains($exception->getMessage(), 'link is invalid')) {
                    session()->flash('error', $exception->getMessage());
                    $profile = AvailabilityCalendar::loadCalendarRelations($profile->fresh('categories'));
                }
            }
        } elseif ($wasGoogleConnected && ! $profile->google_calendar_connected) {
            session()->flash('error', 'Your Google Calendar link is invalid. Click Connect Google Calendar to sign in again.');
        }

        $calendar = AvailabilityCalendar::calendarData($profile);

        return view('performer.availability.index', compact('profile', 'calendar'));
    }


    public function store(Request $request): RedirectResponse
    {
        $profile = Auth::user()->performerProfile;

        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'is_available' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_available'] = $request->boolean('is_available', true);

        $hasConfirmedBooking = Booking::where('performer_id', Auth::id())
            ->overlappingAssignmentDates($validated['date'], $validated['date'])
            ->lockingDate()
            ->exists();

        if ($hasConfirmedBooking) {
            return redirect()
                ->route('performer.availability.index')
                ->with('warning', 'That date already has a confirmed booking, so availability cannot be changed.');
        }

        $declinedPending = 0;

        // Day off / busy means not taking bookings — clear pending requests that day.
        if (! $validated['is_available']) {
            $declinedPending = $this->declinePendingBookingsForDate($validated['date']);
        }

        // it only ever stores exceptions to the default, not every single day
        $isDefaultAvailable = $validated['is_available']
            && empty($validated['start_time'])
            && empty($validated['end_time'])
            && empty($validated['notes']);

        if ($isDefaultAvailable) {
            $profile->availabilitySchedules()->whereDate('date', $validated['date'])->delete();

            return redirect()
                ->route('performer.availability.index')
                ->with('success', 'Date is available by default.');
        }

        $profile->availabilitySchedules()->updateOrCreate(
            ['date' => $validated['date']],
            $validated
        );

        $success = 'Availability updated.';

        if ($declinedPending > 0) {
            $success = 'Availability updated. Pending booking request(s) on that day were declined.';
        }

        return redirect()
            ->route('performer.availability.index')
            ->with('success', $success);
    }

    public function destroy(AvailabilitySchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->performer_profile_id === Auth::user()->performerProfile->id, 403);

        $hasConfirmedBooking = Booking::where('performer_id', Auth::id())
            ->overlappingAssignmentDates($schedule->date, $schedule->date)
            ->lockingDate()
            ->exists();

        if ($hasConfirmedBooking) {
            return redirect()
                ->route('performer.availability.index')
                ->with('warning', 'That date already has a booking, so it cannot be cleared back to available.');
        }

        $schedule->delete();

        return redirect()
            ->route('performer.availability.index')
            ->with('success', 'Date cleared — available by default again.');
    }

    private function declinePendingBookingsForDate(string $date): int
    {
        $pendingBookings = Booking::query()
            ->with('organizer')
            ->where('performer_id', Auth::id())
            ->overlappingAssignmentDates($date, $date)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingBookings as $booking) {
            $booking->update(['status' => 'rejected']);

            if ($booking->event_id) {
                EventApplication::where('event_id', $booking->event_id)
                    ->where('performer_id', $booking->performer_id)
                    ->update(['status' => 'declined']);
            }

            if ($booking->organizer) {
                Notification::send(
                    $booking->organizer,
                    'booking',
                    'Booking Rejected',
                    Auth::user()->name.' declined your booking for '.$booking->event_name.' (marked unavailable that day).',
                    route('organizer.bookings.show', $booking)
                );
            }
        }

        return $pendingBookings->count();
    }

    private function clearStaleBookingBlocks(PerformerProfile $profile): void
    {
        $bookings = $profile->bookings;

        foreach ($profile->availabilitySchedules as $schedule) {
            if ($schedule->is_available || ! filled($schedule->notes)) {
                continue;
            }

            $date = $schedule->date->toDateString();

            $hasConfirmed = $bookings->contains(function ($booking) use ($date) {
                return $booking->locksTheDate() && $booking->assignmentCoversDate($date);
            });

            if ($hasConfirmed) {
                continue;
            }

            $copiedFromOpenBooking = $bookings->contains(
                function ($booking) use ($date, $schedule) {
                    if (! $booking->event_date) {
                        return false;
                    }

                    return in_array($booking->status, ['pending', 'accepted'], true)
                        && $booking->assignmentCoversDate($date)
                        && strcasecmp(trim((string) $booking->event_name), trim((string) $schedule->notes)) === 0;
                }
            );

            if ($copiedFromOpenBooking) {
                $schedule->delete();
            }
        }
    }
}



