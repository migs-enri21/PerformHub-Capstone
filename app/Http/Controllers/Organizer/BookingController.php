<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\Notification;
use App\Models\PerformerProfile;
use App\Services\SupabaseStorageService;
use App\Services\SignWellService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request, PerformerProfile $performer): View
    {
        $events = Event::where('organizer_id', Auth::id())->latest()->get();
        $selectedEvent = $this->getSelectedEvent($request);
        $existingBooking = $this->findActiveBooking($performer, $selectedEvent);
        $fromApplication = $request->boolean('from_application');

        return view('organizer.bookings.create', compact(
            'performer',
            'events',
            'selectedEvent',
            'existingBooking',
            'fromApplication'
        ));
    }

    public function store(Request $request, PerformerProfile $performer): RedirectResponse
    {
        $validated = $this->validateBooking($request);

        $event = Event::find($validated['event_id']);
        $existingBooking = $this->findActiveBooking($performer, $event);

        if ($existingBooking) {
            return back()->with('error', $this->existingBookingMessage($existingBooking));
        }

        $sameDayBooking = Booking::where('performer_id', $performer->user_id)
            ->whereDate('event_date', $validated['event_date'])
            ->whereIn('status', ['accepted', 'completed'])
            ->first();

        if ($sameDayBooking) {
            return back()->with(
                'error',
                'This performer already has "'.$sameDayBooking->event_name.'" on that date. PerformHub allows 1 event per day.'
            );
        }

        $validated['organizer_id'] = Auth::id();
        $validated['performer_id'] = $performer->user_id;
        $fromApplication = $request->boolean('from_application');
        $validated['source'] = $fromApplication ? 'application' : 'invite';
        $validated['status'] = $fromApplication ? 'accepted' : 'pending';

        $booking = Booking::create($validated);

        $this->updateApplicationStatus($booking, $fromApplication);
        $this->sendBookingNotification($booking, $performer, $fromApplication);

        $successMessage = 'Booking request sent.';

        if ($fromApplication) {
            $successMessage = 'Application accepted. Upload the contract for the performer.';
        }

        return redirect()
            ->route('organizer.bookings.show', $booking)
            ->with('success', $successMessage);
    }

    public function show(Booking $booking): View
    {
        $this->ensureBookingOwner($booking);
        $booking->load('performer.performerProfile');

        return view('organizer.bookings.show', compact('booking'));
    }

    public function uploadContract(Request $request, Booking $booking, SignWellService $signWell): RedirectResponse
    {
        $this->ensureBookingOwner($booking);

        if ($booking->signwell_document_id) {
            return back()->with('warning', 'This contract has already been sent through SignWell and cannot be replaced.');
        }

        $file = $request->validate([
            'contract' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ])['contract'];

        $this->saveContract($booking, $file);

        if ($booking->status === 'accepted') {
            if ($signWell->isConfigured()) {
                try {
                    $signWell->sendContractForSignature($booking);
                    $this->sendContractNotification($booking, true);

                    return back()->with('success', 'Contract uploaded. The performer was notified to sign it inside PerformHub.');
                } catch (\RuntimeException $exception) {
                    return back()->with('warning', 'Contract uploaded, but SignWell could not prepare it: '.$exception->getMessage());
                }
            }

            $this->sendContractNotification($booking, false);
        }

        return back()->with('success', 'Contract uploaded. Add the SignWell API key to send it for e-signature.');
    }

    public function sendForSignature(Booking $booking, SignWellService $signWell): RedirectResponse
    {
        $this->ensureBookingOwner($booking);
        abort_unless($booking->status === 'accepted' && $booking->hasContract(), 400);

        if ($booking->signwell_document_id) {
            return back()->with('warning', 'This contract was already sent through SignWell.');
        }

        try {
            $signWell->sendContractForSignature($booking);
            $this->sendContractNotification($booking, true);
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Contract is ready for the performer to sign inside PerformHub.');
    }

    public function syncSignatureStatus(Booking $booking, SignWellService $signWell): RedirectResponse
    {
        $this->ensureBookingOwner($booking);

        try {
            $isNewlyCompleted = $signWell->syncStatus($booking);
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if ($isNewlyCompleted) {
            return back()->with('success', 'The signed contract was received from SignWell. You can now confirm the booking.');
        }

        return back()->with('success', 'SignWell status updated: '.ucfirst($booking->fresh()->signwell_status).'.');
    }

    public function complete(Booking $booking, SignWellService $signWell): RedirectResponse
    {
        $this->ensureBookingOwner($booking);
        abort_unless($booking->status === 'accepted', 400);

        if ($booking->signwell_document_id && ! $booking->hasSignedContract()) {
            try {
                $signWell->syncStatus($booking);
                $booking->refresh();
            } catch (\RuntimeException $exception) {
                return back()->with('error', $exception->getMessage());
            }
        }

        if (! $booking->hasSignedContract()) {
            return back()->with('warning', 'Wait for the signed contract before confirming this booking.');
        }

        $booking->update(['status' => 'completed']);

        return back()->with('success', 'Booking marked as completed.');
    }

    private function getSelectedEvent(Request $request): ?Event
    {
        if (! $request->filled('event')) {
            return null;
        }

        return Event::where('organizer_id', Auth::id())->find($request->event);
    }

    private function validateBooking(Request $request): array
    {
        return $request->validate([
            'event_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'requirements' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'event_id' => [
                'required',
                Rule::exists('events', 'id')->where(function ($query) {
                    return $query->where('organizer_id', Auth::id());
                }),
            ],
        ]);
    }

    private function findActiveBooking(PerformerProfile $performer, ?Event $event): ?Booking
    {
        if (! $event) {
            return null;
        }

        return Booking::where('organizer_id', Auth::id())
            ->where('performer_id', $performer->user_id)
            ->where('event_id', $event->id)
            ->whereIn('status', ['pending', 'accepted', 'completed'])
            ->first();
    }

    private function existingBookingMessage(Booking $booking): string
    {
        if ($booking->status === 'pending') {
            return 'A booking request has already been sent to this performer for this event.';
        }

        return 'This performer is already booked for this event.';
    }

    private function updateApplicationStatus(Booking $booking, bool $appliedFirst): void
    {
        EventApplication::where('event_id', $booking->event_id)
            ->where('performer_id', $booking->performer_id)
            ->update(['status' => $appliedFirst ? 'accepted' : 'invited']);
    }

    private function sendBookingNotification(Booking $booking, PerformerProfile $performer, bool $appliedFirst): void
    {
        if ($appliedFirst) {
            Notification::send(
                $performer->user,
                'booking',
                'Application Accepted',
                Auth::user()->name.' accepted your application for '.$booking->event_name.'. Wait for the contract, then upload the signed copy.',
                route('performer.bookings.show', $booking)
            );

            return;
        }

        Notification::send(
            $performer->user,
            'booking',
            'New Booking Request',
            Auth::user()->name.' sent you a booking request for '.$booking->event_name,
            route('performer.bookings.show', $booking)
        );
    }

    private function ensureBookingOwner(Booking $booking): void
    {
        abort_unless($booking->organizer_id === Auth::id(), 403);
    }

    private function saveContract(Booking $booking, $file): void
    {
        $storage = new SupabaseStorageService();

        if ($booking->contract_path) {
            $storage->delete('organizer-files', $booking->contract_path);
        }

        if ($booking->signed_contract_path) {
            $storage->delete('organizer-files', $booking->signed_contract_path);
        }

        $path = $storage->upload($file, 'organizer-files', 'contract', Auth::id());

        $booking->update([
            'contract_path' => $path,
            'signed_contract_path' => null,
            'signed_contract_uploaded_at' => null,
            'signwell_document_id' => null,
            'signwell_status' => null,
            'signwell_signing_url' => null,
            'signwell_sent_at' => null,
            'signwell_completed_at' => null,
            'performer_confirmed_contract' => false,
            'contract_confirmed_at' => null,
        ]);
    }

    private function sendContractNotification(Booking $booking, bool $isElectronic): void
    {
        $message = 'A contract has been uploaded for '.$booking->event_name;

        if ($isElectronic) {
            $message = 'A contract is ready for you to sign inside PerformHub for '.$booking->event_name.'.';
        }

        Notification::send(
            $booking->performer,
            'contract',
            'Contract Uploaded',
            $message,
            route('performer.bookings.show', $booking)
        );
    }
}
