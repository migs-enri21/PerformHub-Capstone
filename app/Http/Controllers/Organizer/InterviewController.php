<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Interview;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function index()
    {
        return view('organizer.interviews.index');
    }
    public function create(Booking $booking): View
    {
        abort_unless($booking->organizer_id === Auth::id(), 403);

        return view('organizer.interviews.create', compact('booking'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->organizer_id === Auth::id(), 403);

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $roomId = 'performhub-'.Str::slug($booking->event_name).'-'.$booking->id.'-'.Str::random(6);

        $interview = Interview::create([
            'booking_id' => $booking->id,
            'organizer_id' => Auth::id(),
            'performer_id' => $booking->performer_id,
            'scheduled_at' => $validated['scheduled_at'],
            'jitsi_room_id' => $roomId,
            'notes' => $validated['notes'] ?? null,
            'status' => 'scheduled',
        ]);

        $booking->update(['status' => 'interview_scheduled']);

        Notification::send(
            $booking->performer,
            'interview',
            'Interview Scheduled',
            'An interview has been scheduled for '.$booking->event_name,
            route('interviews.join', $interview)
        );

        return redirect()->route('organizer.bookings.show', $booking)
            ->with('success', 'Interview scheduled.');
    }
}
