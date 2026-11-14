<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function bookings(Request $request): View
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
            $query->whereDate('assigned_end_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('assigned_start_date', '<=', $request->date_to);
        }

        $bookings = match ($request->query('sort', 'name_asc')) {
            'latest' => $query->latest(),
            'name_desc' => $query->orderByRaw('LOWER(event_name) DESC')->orderBy('assigned_start_date'),
            default => $query->orderByRaw('LOWER(event_name) ASC')->orderBy('assigned_start_date'),
        };
        $bookings = $bookings->paginate(20)->withQueryString();
        $organizers = User::where('role', User::ROLE_ORGANIZER)->orderBy('first_name')->get();

        $eventPerformers = Booking::query()
            ->whereNotNull('event_id')
            ->with('performer')
            ->get()
            ->groupBy('event_id')
            ->map(fn ($eventBookings) => $eventBookings
                ->filter(fn ($booking) => $booking->performer)
                ->unique('performer_id')
                ->map(fn ($booking) => [
                    'name' => $booking->performer->fullName(),
                    'email' => $booking->performer->email,
                ])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all()
            );

        return view('admin.monitoring.bookings', compact('bookings', 'organizers', 'eventPerformers'));
    }
}
