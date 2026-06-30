<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::where('performer_id', Auth::id())
            ->with(['organizer.organizerProfile', 'interview'])
            ->latest()
            ->paginate(10);

        return view('performer.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        abort_unless($booking->performer_id === Auth::id(), 403);
        $booking->load(['organizer.organizerProfile', 'interview']);

        return view('performer.bookings.show', compact('booking'));
    }

    public function accept(Booking $booking): RedirectResponse
    {
        abort_unless($booking->performer_id === Auth::id(), 403);
        abort_unless($booking->status === 'pending', 400);

        $booking->update(['status' => 'accepted']);
        Notification::send(
            $booking->organizer,
            'booking',
            'Booking Accepted',
            Auth::user()->name.' accepted your booking for '.$booking->event_name,
            route('organizer.bookings.show', $booking)
        );

        return back()->with('success', 'Booking accepted.');
    }

    public function reject(Booking $booking): RedirectResponse
    {
        abort_unless($booking->performer_id === Auth::id(), 403);
        abort_unless(in_array($booking->status, ['pending', 'interview_scheduled']), 400);

        $booking->update(['status' => 'rejected']);
        Notification::send(
            $booking->organizer,
            'booking',
            'Booking Rejected',
            Auth::user()->name.' declined your booking for '.$booking->event_name,
            route('organizer.bookings.show', $booking)
        );

        return back()->with('success', 'Booking rejected.');
    }

    public function confirmContract(Booking $booking): RedirectResponse
    {
        abort_unless($booking->performer_id === Auth::id(), 403);
        abort_unless($booking->contract_path, 400);

        $booking->update([
            'performer_confirmed_contract' => true,
            'contract_confirmed_at' => now(),
        ]);

        return back()->with('success', 'Contract confirmed.');
    }
}
