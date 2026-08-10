<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\PerformerProfile;
use App\Models\Review;
use App\Support\AvailabilityCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PerformerSearchController extends Controller
{
    public function index(Request $request): View
    {
        $selectedEvent = $this->getSelectedEvent($request);
        $performers = $this->getPerformers($request, $selectedEvent);
        $categories = Category::where('is_active', true)->get();

        return view('organizer.performers.index', compact('performers', 'categories', 'selectedEvent'));
    }

    public function show(PerformerProfile $performer): View
    {
        $performer = AvailabilityCalendar::loadCalendarRelations(
            $performer->load(['user', 'categories', 'portfolios'])
        );
        $calendar = AvailabilityCalendar::calendarData($performer);
        $reviews = Review::where('reviewee_id', $performer->user_id)
            ->with('reviewer')
            ->latest()
            ->get();

        return view('organizer.performers.show', compact('performer', 'reviews', 'calendar'));
    }

    private function getSelectedEvent(Request $request): ?Event
    {
        if (! $request->filled('event')) {
            return null;
        }

        return Event::where('organizer_id', Auth::id())->find($request->event);
    }

    private function getPerformers(Request $request, ?Event $selectedEvent)
    {
        $query = PerformerProfile::query()
            ->with(['user', 'categories'])
            ->whereHas('user', fn ($user) => $user->where('is_active', true));

        $this->applySearchFilter($query, $request->search);
        $this->applyCategoryFilter($query, $request->category_id);
        $this->applyGenreFilter($query, $request->genre);
        $this->applyRatingFilter($query, $request->min_rating);

        $date = $request->available_date ?: $selectedEvent?->event_date;
        $this->applyAvailabilityFilter($query, $date);

        return $query->latest()->paginate(12)->withQueryString();
    }

    private function applySearchFilter($query, ?string $search): void
    {
        if (! $search) {
            return;
        }

        $query->where(function ($performer) use ($search) {
            $performer->where('stage_name', 'like', "%{$search}%")
                ->orWhere('genre', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        });
    }

    private function applyCategoryFilter($query, $categoryId): void
    {
        if (! $categoryId) {
            return;
        }

        $query->whereHas('categories', fn ($category) => $category->where('categories.id', $categoryId));
    }

    private function applyGenreFilter($query, ?string $genre): void
    {
        if ($genre) {
            $query->where('genre', $genre);
        }
    }

    private function applyRatingFilter($query, $rating): void
    {
        if (! $rating) {
            return;
        }

        $query->whereIn('user_id', function ($reviews) use ($rating) {
            $reviews->select('reviewee_id')
                ->from('reviews')
                ->groupBy('reviewee_id')
                ->havingRaw('AVG(rating) >= ?', [$rating]);
        });
    }

    private function applyAvailabilityFilter($query, ?string $date): void
    {
        if (! $date) {
            return;
        }

        $query->where(function ($performer) use ($date) {
            $performer->whereDoesntHave('bookings', fn ($booking) => $booking
                ->whereDate('event_date', $date)
                ->whereIn('status', ['pending', 'accepted', 'completed']))
                ->where(function ($schedule) use ($date) {
                    $schedule->whereDoesntHave('availabilitySchedules', fn ($item) => $item->whereDate('date', $date))
                        ->orWhereHas('availabilitySchedules', fn ($item) => $item
                            ->whereDate('date', $date)
                            ->where('is_available', true));
                })
                ->whereDoesntHave('googleCalendarBusyDates', fn ($busyDate) => $busyDate->whereDate('date', $date));
        });
    }
}
