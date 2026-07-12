<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PerformerProfile;
use App\Models\Review;
use App\Support\AvailabilityCalendar;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Event;

class PerformerSearchController extends Controller
{
    public function index(Request $request): View
    {

        $selectedEvent = null;

        if ($request->filled('event')) {

        $selectedEvent = Event::find($request->event);

            if ($selectedEvent) {

            $request->merge([
            'available_date' => $selectedEvent->event_date,
            ]);
            }
        }

        
        if ($selectedEvent && $selectedEvent->eventType) {
        $request->merge([
        'category' => $selectedEvent->eventType->id,
        ]);
        }

        $query = PerformerProfile::query()
            ->with(['user', 'categories'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('stage_name', 'like', "%{$search}%")
                    ->orWhere('genre', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->category_id));
        }

        if ($request->filled('genre')) {
            $query->where('genre', $request->genre);
        }

        if ($request->filled('min_rating')) {
            $query->whereIn('user_id', function ($sub) use ($request) {
                $sub->select('reviewee_id')
                    ->from('reviews')
                    ->groupBy('reviewee_id')
                    ->havingRaw('AVG(rating) >= ?', [$request->min_rating]);
            });
        }

        if ($request->filled('available_date')) {
            $date = $request->available_date;
            $query->where(function ($availabilityQuery) use ($date) {
                $availabilityQuery
                    ->whereDoesntHave('bookings', fn ($booking) => $booking
                        ->whereDate('event_date', $date)
                        ->whereIn('status', ['pending', 'accepted', 'completed']))
                    ->where(function ($scheduleQuery) use ($date) {
                        $scheduleQuery
                            ->whereDoesntHave('availabilitySchedules', fn ($schedule) => $schedule->whereDate('date', $date))
                            ->orWhereHas('availabilitySchedules', fn ($schedule) => $schedule
                                ->whereDate('date', $date)
                                ->where('is_available', true));
                    })
                    ->whereDoesntHave('googleCalendarBusyDates', fn ($busyDate) => $busyDate->whereDate('date', $date));
            });
        }

        $performers = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('organizer.performers.index', compact('performers', 'categories', 'selectedEvent'));
    }

    public function show(PerformerProfile $performer): View
    {
        $performer = AvailabilityCalendar::loadCalendarRelations(
            $performer->load(['user', 'categories', 'portfolios'])
        );
        $calendar = AvailabilityCalendar::calendarData($performer);
        $reviews = Review::where('reviewee_id', $performer->user_id)->with('reviewer')->latest()->get();

        return view('organizer.performers.show', compact('performer', 'reviews', 'calendar'));
    }
}
