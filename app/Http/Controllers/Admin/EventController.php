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
            $query->whereDate('event_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('event_date', '<=', $request->date_to);
        }

        $events = $query->latest()->paginate(15);
        $organizers = User::where('role', User::ROLE_ORGANIZER)->orderBy('first_name')->get();

        return view('admin.events.index', compact('events', 'organizers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'event_name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'requirements' => ['nullable', 'string', 'max:2000'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:24'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'organizer_id' => ['required', 'exists:users,id'],
            'performer_id' => ['required', 'exists:users,id'],
            'status' => ['nullable', 'in:pending,interview_scheduled,accepted,rejected,completed'],
        ]);

        Booking::create([
            ...$validated,
            'status' => $validated['status'] ?? 'pending',
        ]);

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function show(Booking $booking): View
    {
        $booking->load(['organizer', 'performer', 'interview']);

        return view('admin.events.show', compact('booking'));
    }
}
