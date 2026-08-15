<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\EventApplication;
use App\Services\SupabaseStorageService;
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

    // copied idea from PerformerSearchController
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

        if ($booking->event_id) {
            EventApplication::where('event_id', $booking->event_id)
                ->where('performer_id', $booking->performer_id)
                ->update(['status' => 'accepted']);
        }

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

        if ($booking->event_id) {
            EventApplication::where('event_id', $booking->event_id)
                ->where('performer_id', $booking->performer_id)
                ->update(['status' => 'declined']);
        }

        Notification::send(
            $booking->organizer,
            'booking',
            'Booking Rejected',
            Auth::user()->name.' declined your booking for '.$booking->event_name,
            route('organizer.bookings.show', $booking)
        );

        return back()->with('success', 'Booking rejected.');
    }

    public function uploadSignedContract(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->performer_id === Auth::id(), 403);
        abort_unless($booking->status === 'accepted' && $booking->hasContract(), 400);

        $file = $request->validate([
            'signed_contract' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ])['signed_contract'];

        $storage = new SupabaseStorageService();

        if ($booking->signed_contract_path) {
            $storage->delete('organizer-files', $booking->signed_contract_path);
        }

        $path = $storage->upload($file, 'organizer-files', 'signed_contract', Auth::id());

        $booking->update([
            'signed_contract_path' => $path,
            'signed_contract_uploaded_at' => now(),
        ]);

        Notification::send(
            $booking->organizer,
            'contract',
            'Signed Contract Uploaded',
            Auth::user()->name.' uploaded the signed contract for '.$booking->event_name,
            route('organizer.bookings.show', $booking)
        );

        return back()->with('success', 'Signed contract sent to the organizer.');
    }
}
