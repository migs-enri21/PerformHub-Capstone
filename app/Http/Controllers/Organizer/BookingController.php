<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Event;
use App\Models\PerformerProfile;
use App\Services\SupabaseStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function create(PerformerProfile $performer): View
    {
        $events = Event::where('organizer_id', Auth::id())->latest()->get();

        return view('organizer.bookings.create', compact('performer', 'events'));
    }

    public function store(Request $request, PerformerProfile $performer): RedirectResponse
    {
        $validated = $request->validate([
            'event_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'requirements' => ['nullable', 'string', 'max:2000'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:24'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = Booking::create([
            ...$validated,
            'organizer_id' => Auth::id(),
            'performer_id' => $performer->user_id,
            'status' => 'pending',
        ]);

        Notification::send(
            $performer->user,
            'booking',
            'New Booking Request',
            Auth::user()->name.' sent you a booking request for '.$booking->event_name,
            route('performer.bookings.show', $booking)
        );

        return redirect()->route('organizer.bookings.show', $booking)
            ->with('success', 'Booking request sent.');
    }

    public function show(Booking $booking): View
    {
        abort_unless($booking->organizer_id === Auth::id(), 403);
        $booking->load('performer.performerProfile');

        return view('organizer.bookings.show', compact('booking'));
    }

    public function uploadContract(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->organizer_id === Auth::id(), 403);
        abort_unless($booking->status === 'accepted', 400, 'Upload a contract only after the performer accepts the booking.');

        $validated = $request->validate([
            'contract' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $supabase = new SupabaseStorageService();

        if ($booking->contract_path) {
            $supabase->delete('organizer-files', $booking->contract_path);
        }

        $path = $supabase->upload($request->file('contract'), 'organizer-files', 'contract', Auth::id());
        $booking->update([
            'contract_path' => $path,
            'performer_confirmed_contract' => false,
            'contract_confirmed_at' => null,
        ]);

        Notification::send(
            $booking->performer,
            'contract',
            'Contract Uploaded',
            'A contract has been uploaded for '.$booking->event_name,
            route('performer.bookings.show', $booking)
        );

        return back()->with('success', 'Contract uploaded.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        abort_unless($booking->organizer_id === Auth::id(), 403);
        abort_unless($booking->status === 'accepted', 400);

        if ($booking->hasContract() && ! $booking->performer_confirmed_contract) {
            return back()->with('warning', 'Wait for the performer to review and confirm the contract before marking this booking complete.');
        }

        $booking->update(['status' => 'completed']);

        return back()->with('success', 'Booking marked as completed.');
    }
}
