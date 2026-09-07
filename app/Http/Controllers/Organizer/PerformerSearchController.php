<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Booking;
use App\Models\Event;
use App\Models\PerformerProfile;
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

        if (! $performer->portfolioVisibleTo(Auth::user())) {
            $performer->setRelation('portfolios', collect());
        }

        $calendar = AvailabilityCalendar::calendarData($performer);
        $bookingMessage = $this->bookingMessage($performer);
        $bookingUrl = $this->bookingUrl($performer, $bookingMessage);

        return view('organizer.performers.show', compact('performer', 'calendar', 'bookingUrl', 'bookingMessage'));
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
            ->whereHas('user', function ($user) {
                return $user->where('is_active', true)
                    ->where('is_verified', true)
                    ->where('onboarding_step', '>=', \App\Models\User::ONBOARDING_COMPLETE);
            })
            ->where('is_verified_badge', true);

        $this->applySearchFilter($query, $request->search);
        $this->applyCategoryFilter($query, $request->category_id);
        $this->applyGenreFilter($query, $request->genre);

        $date = $request->available_date;

        if (! $date && $selectedEvent) {
            $date = $selectedEvent->event_date;
        }
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

        $query->whereHas('categories', function ($category) use ($categoryId) {
            return $category->where('categories.id', $categoryId);
        });
    }

    private function applyGenreFilter($query, ?string $genre): void
    {
        if ($genre) {
            $query->where('genre', $genre);
        }
    }

    private function applyAvailabilityFilter($query, ?string $date): void
    {
        if (! $date) {
            return;
        }

        $query->where(function ($performer) use ($date) {
            $performer->whereDoesntHave('bookings', function ($booking) use ($date) {
                return $booking->whereDate('event_date', $date)
                    ->whereIn('status', ['pending', 'accepted', 'completed']);
            })
                ->where(function ($schedule) use ($date) {
                    $schedule->whereDoesntHave('availabilitySchedules', function ($item) use ($date) {
                        return $item->whereDate('date', $date);
                    })->orWhereHas('availabilitySchedules', function ($item) use ($date) {
                        return $item->whereDate('date', $date)
                            ->where('is_available', true);
                    });
                })
                ->whereDoesntHave('googleCalendarBusyDates', function ($busyDate) use ($date) {
                    return $busyDate->whereDate('date', $date);
                });
        });
    }

    private function bookingUrl(PerformerProfile $performer, ?string $bookingMessage): ?string
    {
        if ($bookingMessage) {
            return null;
        }

        return route('organizer.bookings.create', [
            'performer' => $performer,
            'event' => request('event'),
        ]);
    }

    private function bookingMessage(PerformerProfile $performer): ?string
    {
        $eventId = request('event');

        if (! $eventId) {
            return null;
        }

        $booking = Booking::where('organizer_id', Auth::id())
            ->where('performer_id', $performer->user_id)
            ->where('event_id', $eventId)
            ->whereIn('status', ['pending', 'accepted', 'completed'])
            ->first();

        if (! $booking) {
            return null;
        }

        if ($booking->status === 'pending') {
            return 'Booking request already sent for this event.';
        }

        return 'Already booked for this event.';
    }
}
