<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\AvailabilitySchedule;
use App\Models\Booking;
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
            ->whereDate('event_date', $validated['date'])
            ->whereIn('status', ['accepted', 'completed'])
            ->exists();

        if ($hasConfirmedBooking) {
            return redirect()
                ->route('performer.availability.index')
                ->with('warning', 'That date already has a booking, so it cannot be set as available.');
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

        return redirect()
            ->route('performer.availability.index')
            ->with('success', 'Availability updated.');
    }

    public function destroy(AvailabilitySchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->performer_profile_id === Auth::user()->performerProfile->id, 403);

        $hasConfirmedBooking = Booking::where('performer_id', Auth::id())
            ->whereDate('event_date', $schedule->date)
            ->whereIn('status', ['accepted', 'completed'])
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
}



