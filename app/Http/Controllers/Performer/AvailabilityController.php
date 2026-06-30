<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\AvailabilitySchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('performer.profile.show')->withFragment('availability');
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

        $isDefaultAvailable = $validated['is_available']
            && empty($validated['start_time'])
            && empty($validated['end_time'])
            && empty($validated['notes']);

        if ($isDefaultAvailable) {
            $profile->availabilitySchedules()->whereDate('date', $validated['date'])->delete();

            return redirect()
                ->route('performer.profile.show')
                ->withFragment('availability')
                ->with('success', 'Date is available by default.');
        }

        $profile->availabilitySchedules()->updateOrCreate(
            ['date' => $validated['date']],
            $validated
        );

        return redirect()
            ->route('performer.profile.show')
            ->withFragment('availability')
            ->with('success', 'Availability updated.');
    }

    public function destroy(AvailabilitySchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->performer_profile_id === Auth::user()->performerProfile->id, 403);
        $schedule->delete();

        return redirect()
            ->route('performer.profile.show')
            ->withFragment('availability')
            ->with('success', 'Date cleared — available by default again.');
    }
}
