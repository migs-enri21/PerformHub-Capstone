<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Genre;
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
        $genres = Genre::where('is_active', true)->orderBy('name')->get(['name', 'category_id']);

        return view('organizer.performers.index', compact('performers', 'categories', 'genres', 'selectedEvent'));
    }

    public function show(PerformerProfile $performer): View
    {
        $this->ensureVerifiedPerformer($performer);

        $performer = AvailabilityCalendar::loadCalendarRelations(
            $performer->load(['user', 'categories', 'portfolios'])
        );

        if (!$performer->portfolioVisibleTo(Auth::user())) {
            $performer->setRelation('portfolios', collect());
        }

        $calendar = AvailabilityCalendar::calendarData($performer);
        $bookingMessage = $this->bookingMessage($performer);
        $bookingUrl = $this->bookingUrl($performer, $bookingMessage);

        return view('organizer.performers.show', compact('performer', 'calendar', 'bookingUrl', 'bookingMessage'));
    }

    private function getSelectedEvent(Request $request): ?Event
    {
        if (!$request->filled('event')) {
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
        $this->applySpecialtyFilter($query, $request->specialty);
        $this->applyGenreFilter($query, $request->genre);

        $startDate = $request->available_date;
        $endDate = $request->available_date;

        if (!$startDate && $selectedEvent) {
            $startDate = $selectedEvent->event_date->toDateString();
            $endDate = $selectedEvent->end_date->toDateString();
        }
        $this->applyAvailabilityFilter($query, $startDate, $endDate);

        return $query->orderBy('stage_name')->paginate(12)->withQueryString();
    }

    private function applySearchFilter($query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where(function ($performer) use ($search) {
            $performer->where('stage_name', 'like', "%{$search}%")
                ->orWhere('genre', 'like', "%{$search}%")
                ->orWhere('specialty', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        });
    }

    private function applyCategoryFilter($query, $categoryId): void
    {
        if (!$categoryId) {
            return;
        }

        $query->whereHas('categories', function ($category) use ($categoryId) {
            return $category->where('categories.id', $categoryId);
        });
    }

    private function applyGenreFilter($query, ?string $genre): void
    {
        if ($genre) {
            $query->whereJsonContains('genre', $genre);
        }
    }

    private function applySpecialtyFilter($query, ?string $specialty): void
    {
        if ($specialty) {
            $query->whereJsonContains('specialty', $specialty);
        }
    }

    private function applyAvailabilityFilter($query, ?string $startDate, ?string $endDate): void
    {
        if (!$startDate || !$endDate) {
            return;
        }

        $query->where(function ($performer) use ($startDate, $endDate) {
            $performer->whereDoesntHave('bookings', function ($booking) use ($startDate, $endDate) {
                return $booking->overlappingDates($startDate, $endDate)
                    ->lockingDate();
            })
                ->whereDoesntHave('availabilitySchedules', function ($schedule) use ($startDate, $endDate) {
                    return $schedule->whereBetween('date', [$startDate, $endDate])
                        ->where('is_available', false);
                })
                ->whereDoesntHave('googleCalendarBusyDates', function ($busyDate) use ($startDate, $endDate) {
                    return $busyDate->whereBetween('date', [$startDate, $endDate]);
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

        if (!$eventId) {
            return null;
        }

        $booking = Booking::where('organizer_id', Auth::id())
            ->where('performer_id', $performer->user_id)
            ->where('event_id', $eventId)
            ->whereIn('status', ['pending', 'accepted', 'completed'])
            ->first();

        if (!$booking) {
            return null;
        }

        if ($booking->status === 'pending') {
            return 'Booking request already sent for this event.';
        }

        return 'Already booked for this event.';
    }

    private function ensureVerifiedPerformer(PerformerProfile $performer): void
    {
        $performer->loadMissing('user');

        if (
            ! $performer->user
            || ! $performer->user->is_active
            || ! $performer->user->is_verified
            || ! $performer->user->hasCompletedOnboarding()
            || ! $performer->is_verified_badge
        ) {
            abort(404);
        }
    }
}
