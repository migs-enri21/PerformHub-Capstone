<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class EventApplicationController extends Controller
{
    public function store(Event $event): RedirectResponse
    {
        $user = Auth::user();
        Event::refreshStatuses();
        $event->refresh();

        if (strcasecmp($event->status, 'Open') !== 0) {
            return back()->with('error', 'This event is no longer accepting applications.');
        }

        if ($event->hasStarted()) {
            return back()->with('error', 'Applications close when the event starts.');
        }

        if ($user->hasLimitedAccess() || $user->isAwaitingVerification()) {
            return back()->with('warning', 'Your account is under review. You can apply to events after an admin verifies you.');
        }

        $hasActiveBooking = Booking::where('event_id', $event->id)
            ->where('performer_id', $user->id)
            ->whereIn('status', ['pending', 'accepted', 'completed'])
            ->exists();

        if ($hasActiveBooking) {
            return back()->with('warning', 'You already have an active booking for this event.');
        }

        $dateConflict = Booking::bookedDateConflictForEvent($user->id, $event);

        if ($dateConflict) {
            return back()->with(
                'warning',
                'You already have "'.$dateConflict->event_name.'" booked on '.$dateConflict->assignmentDateRangeLabel('M j, Y').'. PerformHub allows 1 booked event per day.'
            );
        }

        $existing = EventApplication::where('event_id', $event->id)
            ->where('performer_id', $user->id)
            ->first();

        if ($existing) {
            // Stale row after a cancelled booking — remove so they can apply again.
            if (in_array($existing->status, ['accepted', 'invited'], true)) {
                $existing->delete();
            } else {
                return back()->with('warning', 'You have already applied to this event.');
            }
        }

        EventApplication::create([
            'event_id' => $event->id,
            'performer_id' => $user->id,
            'status' => 'pending',
        ]);

        if ($user->performerProfile?->stage_name) {
            $performerName = $user->performerProfile->stage_name;
        } else {
            $performerName = $user->name;
        }

        Notification::send(
            $event->organizer,
            'event_application',
            'New event application',
            "{$performerName} applied to your event \"{$event->title}\".",
            route('organizer.events.show', $event)
        );

        return back()->with('success', 'Your application was submitted.');
    }

    public function destroy(Event $event): RedirectResponse
        
    {
        $application = EventApplication::where('event_id', $event->id)
            ->where('performer_id', Auth::id())
            ->first();

        if (! $application) {
            return back()->with('warning', 'You have not applied to this event.');
        }

        if ($application->status !== 'pending') {
            return back()->with('warning', 'This application can no longer be cancelled.');
        }

        $application->delete();

        return back()->with('success', 'Your application was cancelled.');
    }

}
