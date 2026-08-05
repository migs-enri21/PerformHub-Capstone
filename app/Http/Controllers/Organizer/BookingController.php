<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\Notification;
use App\Models\PerformerProfile;
use App\Services\SupabaseStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::where('organizer_id', Auth::id())
            ->with('performer.performerProfile')
            ->latest()
            ->paginate(10);

        return view('organizer.bookings.index', compact('bookings'));
    }

    public function create(Request $request, PerformerProfile $performer): View
    {
        $events = Event::where('organizer_id', Auth::id())->latest()->get();
        $selectedEvent = $this->getSelectedEvent($request);

        return view('organizer.bookings.create', compact('performer', 'events', 'selectedEvent'));
    }

    public function store(Request $request, PerformerProfile $performer): RedirectResponse
    {
        $validated = $this->validateBooking($request);

        if ($this->hasPendingBooking($performer, $validated['event_id'])) {
            return back()->with('error', 'A booking request has already been sent to this performer for this event.');
        }

        $booking = Booking::create([
            ...$validated,
            'organizer_id' => Auth::id(),
            'performer_id' => $performer->user_id,
            'status' => 'pending',
        ]);

        $this->updateApplicationStatus($booking);
        $this->sendBookingNotification($booking, $performer);

        return redirect()
            ->route('organizer.bookings.show', $booking)
            ->with('success', 'Booking request sent.');
    }

    public function show(Booking $booking): View
    {
        $this->ensureBookingOwner($booking);
        $booking->load('performer.performerProfile');

        return view('organizer.bookings.show', compact('booking'));
    }

    public function uploadContract(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureBookingOwner($booking);
        $file = $request->validate([
            'contract' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ])['contract'];

        $this->saveContract($booking, $file);

        if ($booking->status === 'accepted') {
            $this->sendContractNotification($booking);
        }

        return back()->with('success', 'Contract uploaded.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        $this->ensureBookingOwner($booking);
        abort_unless($booking->status === 'accepted', 400);

        if ($booking->hasContract() && ! $booking->performer_confirmed_contract) {
            return back()->with('warning', 'Wait for the performer to review and confirm the contract before marking this booking complete.');
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
                Rule::exists('events', 'id')->where(fn ($query) => $query->where('organizer_id', Auth::id())),
            ],
        ]);
    }

    private function hasPendingBooking(PerformerProfile $performer, int $eventId): bool
    {
        return Booking::where('organizer_id', Auth::id())
            ->where('performer_id', $performer->user_id)
            ->where('event_id', $eventId)
            ->where('status', 'pending')
            ->exists();
    }

    private function updateApplicationStatus(Booking $booking): void
    {
        EventApplication::where('event_id', $booking->event_id)
            ->where('performer_id', $booking->performer_id)
            ->update(['status' => 'invited']);
    }

    private function sendBookingNotification(Booking $booking, PerformerProfile $performer): void
    {
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

        $path = $storage->upload($file, 'organizer-files', 'contract', Auth::id());

        $booking->update([
            'contract_path' => $path,
            'performer_confirmed_contract' => false,
            'contract_confirmed_at' => null,
        ]);
    }

    private function sendContractNotification(Booking $booking): void
    {
        Notification::send(
            $booking->performer,
            'contract',
            'Contract Uploaded',
            'A contract has been uploaded for '.$booking->event_name,
            route('performer.bookings.show', $booking)
        );
    }
}
