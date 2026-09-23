<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\EventApplication;
use App\Services\SupabaseStorageService;
use App\Services\SignWellService;
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
        $dayConflict = $booking->status === 'pending'
            ? $booking->sameDayConfirmedConflict()
            : null;

        return view('performer.bookings.show', compact('booking', 'dayConflict'));
    }

    public function accept(Booking $booking, SignWellService $signWell): RedirectResponse
    {
        abort_unless($booking->performer_id === Auth::id(), 403);
        abort_unless($booking->status === 'pending', 400);

        $conflict = $booking->sameDayConfirmedConflict();

        if ($conflict) {
            return back()->with(
                'warning',
                'You already have "'.$conflict->event_name.'" on '.$booking->event_date->format('F d, Y').'. PerformHub allows 1 event per day.'
            );
        }

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

        $message = 'Booking accepted.';

        if ($booking->hasContract() && ! $booking->signwell_document_id && $signWell->isConfigured()) {
            try {
                $signWell->sendContractForSignature($booking);
                $message = 'Booking accepted. Your contract is ready for electronic signature.';
            } catch (\RuntimeException $exception) {
                $message = 'Booking accepted. The organizer contract is available, but electronic signing is not ready yet.';
            }
        }

        return back()->with('success', $message);
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

    public function requestCancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->performer_id === Auth::id(), 403);

        if (! $booking->canRequestCancel()) {
            return back()->with('warning', 'This booking can no longer be cancelled.');
        }

        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ]);

        $booking->update([
            'cancel_reason' => $validated['cancel_reason'],
            'cancel_requested_at' => now(),
        ]);

        Notification::send(
            $booking->organizer,
            'booking',
            'Cancel Request',
            Auth::user()->name.' asked to cancel '.$booking->event_name.'.',
            route('organizer.bookings.show', $booking)
        );

        return back()->with('success', 'Cancel request sent to the organizer.');
    }

    public function uploadSignedContract(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->performer_id === Auth::id(), 403);
        abort_unless($booking->status === 'accepted' && $booking->hasContract(), 400); // bad request

        if ($booking->signwell_document_id) {
            return back()->with('warning', 'This contract must be signed through the SignWell signing screen.');
        }

        $file = $request->validate([
            'signed_contract' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ])['signed_contract'];

        if ($booking->sameDayConfirmedConflict()) {
            return back()->with(
                'warning',
                'You already have a signed booking on '.$booking->event_date->format('F d, Y').'. PerformHub allows 1 event per day.'
            );
        }

        $storage = new SupabaseStorageService();

        if ($booking->signed_contract_path) {
            $storage->delete('organizer-files', $booking->signed_contract_path);
        }

        $path = $storage->upload($file, 'organizer-files', 'signed_contract', Auth::id());

        $booking->update([
            'signed_contract_path' => $path,
            'signed_contract_uploaded_at' => now(),
        ]);

        $booking->markCompletedFromSignature();

        Notification::send(
            $booking->organizer,
            'contract',
            'Contract Signed',
            Auth::user()->name.' signed the contract for '.$booking->event_name.'. The date is now booked.',
            route('organizer.bookings.show', $booking)
        );

        return back()->with('success', 'Signed contract sent. This date is now booked.');
    }

    public function signContract(Booking $booking, SignWellService $signWell)
    {
        abort_unless($booking->performer_id === Auth::id(), 403);
        abort_unless($booking->status === 'accepted' && $booking->signwell_document_id, 400);

        try {
            $signingUrl = $signWell->signingUrl($booking);
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('performer.bookings.show', $booking)
                ->with('error', $exception->getMessage());
        }

        if (! $signingUrl) {
            return redirect()
                ->route('performer.bookings.show', $booking)
                ->with('warning', 'The signing screen is not ready yet. Please try again shortly.');
        }

        return view('performer.bookings.sign', compact('booking', 'signingUrl'));
    }

    public function syncElectronicSignature(Booking $booking, SignWellService $signWell): RedirectResponse
    {
        abort_unless($booking->performer_id === Auth::id(), 403);

        try {
            $signedContractSaved = $signWell->syncStatus($booking);
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('performer.bookings.show', $booking)
                ->with('warning', 'Your signature was completed. The signed PDF is still being prepared, so check the booking again shortly.');
        }

        if ($signedContractSaved) {
            Notification::send(
                $booking->organizer,
                'contract',
                'Contract Electronically Signed',
                Auth::user()->name.' signed the contract for '.$booking->event_name.'. The date is now booked.',
                route('organizer.bookings.show', $booking)
            );
        }

        return redirect()
            ->route('performer.bookings.show', $booking)
            ->with('success', 'Electronic signature completed. This date is now booked.');
    }
}
