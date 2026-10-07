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
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request, PerformerProfile $performer): View
    {
        $this->ensurePerformerCanBeBooked($performer);
        Event::refreshStatuses();

        $events = $this->activeEvents();
        $selectedEvent = $this->getSelectedEvent($request);
        $existingBooking = $this->findActiveBooking($performer, $selectedEvent);
        $fromApplication = $request->boolean('from_application');

        return view('organizer.bookings.create', compact('performer','events','selectedEvent','existingBooking','fromApplication'));
    }

    public function store(Request $request, PerformerProfile $performer): RedirectResponse
    {
        $this->ensurePerformerCanBeBooked($performer);
        Event::refreshStatuses();

        $event = $this->activeEvents()->firstWhere('id', $request->input('event_id'));

        if (! $event) {
            return back()->with('error', 'Select an active event before sending a booking request.');
        }

        $validated = $this->validateBooking($request, $event);
        $validated = $this->bookingDetailsFromEvent($event, $validated);

        if ($event->compensation_type === 'contest') {
            $validated['budget'] = null;
        }

        $existingBooking = $this->findActiveBooking($performer, $event);

        if ($existingBooking) {
            return back()->with('error', $this->existingBookingMessage($existingBooking));
        }

        $sameDayBooking = Booking::where('performer_id', $performer->user_id)
            ->whereDate('event_date', $validated['event_date'])
            ->lockingDate()
            ->first();

        if ($sameDayBooking) {
            return back()->with('error',
                'This performer already has "'.$sameDayBooking->event_name.'" on that date. PerformHub allows 1 event per day.'
            );
        }

        $validated['organizer_id'] = Auth::id();
        $validated['performer_id'] = $performer->user_id;
        $fromApplication = $request->boolean('from_application');
        $validated['source'] = 'invite';
        $validated['status'] = 'pending';

        if ($fromApplication) {
            $validated['source'] = 'application';
            $validated['status'] = 'accepted';
        }

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
        Booking::sweepPastBookings();
        $booking->refresh();
        $booking->load('performer.performerProfile');

        return view('organizer.bookings.show', compact('booking'));
    }

    public function uploadContract(Request $request, Booking $booking, SignWellService $signWell): RedirectResponse
    {
        $this->ensureBookingOwner($booking);

        if (! in_array($booking->status, ['pending', 'accepted'], true)) {
            return back()->with('warning', 'Only an active booking can receive a contract.');
        }

        if ($booking->eventDateHasPassed()) {
            return back()->with('warning', 'This booking date has already passed.');
        }

        if (! $signWell->isConfigured()) {
            return back()->with('warning', 'SignWell must be configured before uploading a contract for e-signature.');
        }

        if ($booking->signwell_document_id) {
            return back()->with('warning', 'This contract has already been sent through SignWell and cannot be replaced.');
        }

        $file = $request->validate([
            'contract' => ['required', 'file', 'mimes:pdf', 'max:10240'],
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

        }

        return back()->with('success', 'Contract uploaded. It will be prepared for e-signature when the performer accepts the booking.');
    }

    public function sendForSignature(Booking $booking, SignWellService $signWell): RedirectResponse
    {
        $this->ensureBookingOwner($booking);
        abort_unless($booking->status === 'accepted' && $booking->hasContract(), 400);

        if ($booking->eventDateHasPassed()) {
            return back()->with('warning', 'This booking date has already passed.');
        }

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
            $isNewlySigned = $signWell->syncStatus($booking);
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $booking->refresh();

        if ($isNewlySigned) {
            return back()->with('success', 'The signed contract was received from SignWell. Review it, then confirm the booking.');
        }

        if ($booking->isSigned()) {
            return back()->with('info', 'The signed contract is ready. Confirm the booking when you are ready.');
        }

        return back()->with('warning', 'The performer has not finished signing yet.');
    }

    public function complete(Booking $booking, SignWellService $signWell): RedirectResponse
    {
        $this->ensureBookingOwner($booking);
        abort_unless($booking->status === 'accepted', 400);

        if ($booking->signwell_document_id && ! $booking->isSigned()) {
            try {
                $signWell->syncStatus($booking);
                $booking->refresh();

            } catch (\RuntimeException $exception) {
                return back()->with('error', $exception->getMessage());
        
            }
        }

        if (! $booking->isSigned()) {
            return back()->with('warning', 'Wait for the signed contract before confirming this booking.');
        }

        if (! $this->bookingFitsEventBudget($booking)) {
            return back()->with('warning', 'This booking amount is higher than the event\'s remaining budget.');
        }

        if (! $booking->markCompletedFromSignature()) {
            return back()->with('warning', 'This performer already has a confirmed booking on that date.');
        }

        return back()->with('success', 'Booking marked as completed.');
    }

    public function approveCancel(Booking $booking): RedirectResponse
    {
        $this->ensureBookingOwner($booking);

        if (! $booking->hasCancelRequest()) {
            return back()->with('warning', 'There is no cancellation request to approve.');
        }

        $booking->update(['status' => 'cancelled']);

        Notification::send($booking->performer, 'booking', 'Cancellation Approved',
            'Your cancellation request for '.$booking->event_name.' was approved.',
            route('performer.bookings.show', $booking)
        );

        return back()->with('success', 'Cancellation request approved. The booking is now cancelled.');
    }

    public function declineCancel(Booking $booking): RedirectResponse
    {
        $this->ensureBookingOwner($booking);

        if (! $booking->hasCancelRequest()) {
            return back()->with('warning', 'There is no cancellation request to decline.');
        }

        $booking->update([
            'cancel_reason' => null,
            'cancel_requested_at' => null,
        ]);

        Notification::send($booking->performer, 'booking', 'Cancellation Declined',
            'Your cancellation request for '.$booking->event_name.' was declined. The booking remains active.',
            route('performer.bookings.show', $booking)
        );

        return back()->with('success', 'Cancellation request declined. The booking remains active.');
    }

    private function getSelectedEvent(Request $request): ?Event
    {
        $eventId = $request->input('event');

        if (! $eventId) {
            $eventId = $request->old('event_id');
        }

        if (! $eventId) {
            return null;
        }

        return $this->activeEvents()->firstWhere('id', $eventId);
    }

    private function activeEvents()
    {
        $events = Event::with('eventType')
            ->where('organizer_id', Auth::id())
            ->whereIn('status', ['Open', 'open'])
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        $activeEvents = collect();

        foreach ($events as $event) {
            if (! $event->hasStarted()) {
                $activeEvents->push($event);
            }
        }

        return $activeEvents;
    }

    private function validateBooking(Request $request, Event $event): array
    {
        $rules = [
            'notes' => ['nullable', 'string', 'max:1000'],
            'budget' => ['required', 'numeric', 'min:0'],
            'event_id' => [
                'required',
                Rule::exists('events', 'id')->where(function ($query) {
                    return $query->where('organizer_id', Auth::id());
                }),
            ],
        ];

        if ($event->compensation_type === 'contest') {
            $rules['budget'] = ['nullable', 'numeric', 'min:0'];
        }

        return $request->validate($rules);
    }

    private function bookingDetailsFromEvent(Event $event, array $bookingDetails): array
    {
        $bookingDetails['event_name'] = $event->title;
        $bookingDetails['event_date'] = $event->event_date;
        $bookingDetails['event_time'] = Carbon::parse($event->start_time)->format('H:i');
        $bookingDetails['end_time'] = Carbon::parse($event->end_time)->format('H:i');
        $bookingDetails['venue'] = $event->venue;
        $bookingDetails['requirements'] = $event->description;

        return $bookingDetails;
    }

    private function findActiveBooking(PerformerProfile $performer, ?Event $event): ?Booking
    {
        if (!$event) {
            return null;
        }

        return Booking::where('organizer_id', Auth::id())
            ->where('performer_id', $performer->user_id)
            ->where('event_id', $event->id)
            ->whereIn('status', ['pending', 'accepted', 'completed'])
            ->first();
    }

    private function bookingFitsEventBudget(Booking $booking): bool
    {
        $event = $booking->event;

        if (! $event || ! in_array($event->compensation_type, ['fixed', 'hourly'], true)) {
            return true;
        }

        if ($event->budget === null) {
            return true;
        }

        $allocatedBudget = Booking::where('event_id', $event->id)
            ->where('status', 'completed')
            ->where('id', '!=', $booking->id)
            ->sum('budget');

        return $allocatedBudget + (float) $booking->budget <= (float) $event->budget;
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
        $applicationStatus = 'invited';

        if ($appliedFirst) {
            $applicationStatus = 'accepted';
        }

        EventApplication::where('event_id', $booking->event_id)
            ->where('performer_id', $booking->performer_id)
            ->update(['status' => $applicationStatus]);
    }

    private function sendBookingNotification(Booking $booking, PerformerProfile $performer, bool $appliedFirst): void
    {
        if ($appliedFirst) {
            Notification::send($performer->user, 'booking', 'Application Accepted',
                Auth::user()->name.' accepted your application for '.$booking->event_name.'. Wait for the organizer to prepare the e-signature contract.',
                route('performer.bookings.show', $booking)
            );

            return;
        }

        Notification::send($performer->user, 'booking', 'New Booking Request',
            Auth::user()->name.' sent you a booking request for '.$booking->event_name,
            route('performer.bookings.show', $booking)
        );
    }

    private function ensureBookingOwner(Booking $booking): void
    {
        abort_unless($booking->organizer_id === Auth::id(), 403);
    }

    private function ensurePerformerCanBeBooked(PerformerProfile $performer): void
    {
        $performer->loadMissing('user');

        if (
            !$performer->user
            || !$performer->user->is_active
            || !$performer->user->is_verified
            || !$performer->user->hasCompletedOnboarding()
            || !$performer->is_verified_badge
        ) {
            abort(404);
        }
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

        Notification::send($booking->performer, 'contract', 'Contract Uploaded', $message,
            route('performer.bookings.show', $booking)
        );
    }
}
