<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $query = Booking::query()->with(['organizer', 'performer']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('requirements', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('organizer_id')) {
            $query->where('organizer_id', $request->organizer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('end_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('event_date', '<=', $request->date_to);
        }

        $sort = $request->query('sort', 'name_asc');

        if ($sort === 'latest') {
            $events = $query->latest('bookings.created_at');
        } else {
            $direction = $sort === 'name_desc' ? 'DESC' : 'ASC';
            $events = $query
                ->leftJoin('users as event_performer_users', 'bookings.performer_id', '=', 'event_performer_users.id')
                ->leftJoin('users as event_organizer_users', 'bookings.organizer_id', '=', 'event_organizer_users.id')
                ->select('bookings.*')
                ->orderByRaw("LOWER(COALESCE(event_performer_users.first_name, event_organizer_users.first_name, '')) {$direction}")
                ->orderByRaw("LOWER(COALESCE(event_performer_users.last_name, event_organizer_users.last_name, '')) {$direction}");
        }
        $events = $events->paginate(15)->withQueryString();
        $organizers = User::where('role', User::ROLE_ORGANIZER)->orderBy('first_name')->get();

        return view('admin.events.index', compact('events', 'organizers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'requirements' => ['nullable', 'string', 'max:2000'],
            'duration_hours' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'organizer_id' => ['required', 'exists:users,id'],
            'performer_id' => ['required', 'exists:users,id'],
            'status' => ['nullable', 'in:pending,accepted,rejected,completed'],
        ]);

        if (empty($validated['end_date'])) {
            $validated['end_date'] = $validated['event_date'];
        }

        $status = 'pending';

        if (! empty($validated['status'])) {
            $status = $validated['status'];
        }

        Booking::create([
            ...$validated,
            'status' => $status,
        ]);

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function show(Booking $booking): View
    {
        $booking->load(['organizer', 'performer']);
        $eventPerformers = $booking->event_id
            ? Booking::query()
                ->where('event_id', $booking->event_id)
                ->with('performer')
                ->orderBy('id')
                ->get()
                ->filter(fn ($eventBooking) => $eventBooking->performer)
                ->unique('performer_id')
                ->sortBy(fn ($eventBooking) => mb_strtolower($eventBooking->performer->fullName()))
                ->values()
            : collect([$booking])
                ->filter(fn ($eventBooking) => $eventBooking->performer)
                ->values();

        return view('admin.events.show', compact('booking', 'eventPerformers'));
    }
}
