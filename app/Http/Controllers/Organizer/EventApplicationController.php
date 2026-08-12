<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class EventApplicationController extends Controller
{
    public function decline(Event $event, EventApplication $application): RedirectResponse
    {
        abort_unless($event->organizer_id === Auth::id(), 403);
        abort_unless($application->event_id === $event->id, 404);

        if ($application->status !== 'pending') {
            return back()->with('warning', 'This application has already been processed.');
        }

        $application->update(['status' => 'declined']);

        $performer = $application->performer;
        $performerName = $performer->name;

        if ($performer->performerProfile?->stage_name) {
            $performerName = $performer->performerProfile->stage_name;
        }

        Notification::send(
            $performer,
            'event_application',
            'Application update',
            'Your application for "'.$event->title.'" was declined by the organizer.',
            route('performer.dashboard')
        );

        return back()->with('success', "Declined {$performerName}'s application.");
    }
}
