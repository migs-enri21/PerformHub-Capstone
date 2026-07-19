<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
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

        if (strcasecmp($event->status, 'Open') !== 0) {
            return back()->with('error', 'This event is no longer accepting applications.');
        }

        if ($event->event_date && $event->event_date < now()->toDateString()) {
            return back()->with('error', 'This event has already passed.');
        }

        if ($user->hasLimitedAccess()) {
            return redirect($user->onboardingRoute())
                ->with('warning', 'Complete sign-up to apply for events.');
        }

        $existing = EventApplication::where('event_id', $event->id)
            ->where('performer_id', $user->id)
            ->exists();

        if ($existing) {
            return back()->with('warning', 'You have already applied to this event.');
        }

        EventApplication::create([
            'event_id' => $event->id,
            'performer_id' => $user->id,
            'status' => 'pending',
        ]);

        $performerName = $user->performerProfile?->stage_name ?? $user->name;

        Notification::send(
            $event->organizer,
            'event_application',
            'New event application',
            "{$performerName} applied to your event \"{$event->title}\".",
            route('organizer.events.index')
        );

        return back()->with('success', 'Your application was submitted.');
    }
}
