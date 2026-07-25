<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\EventApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use Illuminate\Http\Request;

class BookingController extends Controller
{

public function index(Request $request): View
{
    $query = Booking::where('performer_id', Auth::id())
        ->with('organizer.organizerProfile')
        ->latest();

    // ← copied idea from PerformerSearchController
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $bookings = $query->paginate(10)->withQueryString();

    return view('performer.bookings.index', compact('bookings'));
}

    public function show(Booking $booking): View
    {
        abort_unless($booking->performer_id === Auth::id(), 403);
        $booking->load('organizer.organizerProfile');

        return view('performer.bookings.show', compact('booking'));
    }

    public function accept(Booking $booking): RedirectResponse
    {
        abort_unless($booking->performer_id === Auth::id(), 403);
        abort_unless($booking->status === 'pending', 400);

        $booking->update(['status' => 'accepted']);

        EventApplication::where('event_id', $booking->event_id)
        ->where('performer_id', $booking->performer_profile_id)
        ->update(['status' => 'accepted',]);

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
        abort_unless($booking->status === 'pending', 400);

        $booking->update(['status' => 'rejected']);

        EventApplication::where('event_id', $booking->event_id)
        ->where('performer_id', $booking->performer_profile_id)
        ->update(['status' => 'declined',]);

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
        abort_unless($booking->canConfirmContract(), 400, 'This contract cannot be confirmed right now.');

        $booking->update([
            'performer_confirmed_contract' => true,
            'contract_confirmed_at' => now(),
        ]);

        Notification::send(
            $booking->organizer,
            'contract',
            'Contract Confirmed',
            Auth::user()->name.' confirmed the contract for '.$booking->event_name,
            route('organizer.bookings.show', $booking)
        );

        return back()->with('success', 'Contract confirmed. The organizer has been notified.');
    }
}
