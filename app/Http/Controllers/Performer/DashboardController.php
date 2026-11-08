<?php

namespace App\Http\Controllers\Performer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $profile = $user->performerProfile;
        Booking::sweepPastBookings();
        $pendingBookings = Booking::where('performer_id', $user->id)->where('status', 'pending')->count();
        $upcomingBookings = Booking::where('performer_id', $user->id)
            ->whereIn('status', ['accepted', 'completed'])
            ->whereDate('event_date', '>=', today())
            ->count();

        $categoryIds = [];

        if ($profile) {
            $categoryIds = $profile->categories()->pluck('categories.id')->all();
        }

        $availableEvents = $this->getRecommendedEvents($categoryIds, $request->input('search'));

        foreach ($availableEvents as $event) {
            if ($event->cover_photo && $event->photos->isEmpty()) {
                $photo = $event->photos()->create([
                    'file_path' => $event->cover_photo,
                    'sort_order' => 0,
                ]);
                $event->setRelation('photos', collect([$photo]));
            }
        }

        $activeBookingsByEvent = Booking::query()
            ->where('performer_id', $user->id)
            ->whereNotNull('event_id')
            ->whereIn('status', ['pending', 'accepted', 'completed'])
            ->orderByRaw("case status when 'completed' then 1 when 'accepted' then 2 else 3 end")
            ->get(['event_id', 'status'])
            ->unique('event_id')
            ->keyBy('event_id');

        $activeBookingEventIds = $activeBookingsByEvent->keys();

        // Heal stale applications only when a booking was cancelled and there is no active booking left.
        $staleCancelledEventIds = Booking::query()
            ->where('performer_id', $user->id)
            ->where('status', 'cancelled')
            ->whereNotNull('event_id')
            ->whereHas('event', function ($query) {
                $query->whereIn('status', ['Open', 'open']);
            })
            ->pluck('event_id')
            ->diff($activeBookingEventIds)
            ->values();

        if ($staleCancelledEventIds->isNotEmpty()) {
            EventApplication::where('performer_id', $user->id)
                ->whereIn('event_id', $staleCancelledEventIds)
                ->whereIn('status', ['accepted', 'invited'])
                ->delete();
        }

        $applicationStatuses = EventApplication::where('performer_id', $user->id)
            ->pluck('status', 'event_id')
            ->all();

        // Feed footer follows bookings too — completed/accepted must not show Apply again.
        foreach ($activeBookingsByEvent as $eventId => $booking) {
            $applicationStatuses[$eventId] = $booking->status === 'pending' ? 'invited' : 'accepted';
        }

        $pendingBookingUrls = Booking::where('performer_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('event_id')
            ->get()
            ->mapWithKeys(function (Booking $booking) {
            return [
                $booking->event_id => route('performer.bookings.show', $booking),
            ];
        })
            ->all();
        $cancelRequests = Booking::where('performer_id', $user->id)
            ->cancelRequested()
            ->count();

        $nextBooking = Booking::where('performer_id', $user->id)
            ->whereIn('status', ['accepted', 'completed'])
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->orderBy('event_time')
            ->first();

        $recentNotifications = $user->notifications()->latest()->take(3)->get();

        return view('performer.dashboard', compact(
            'profile',
            'pendingBookings',
            'upcomingBookings',
            'cancelRequests',
            'availableEvents',
            'applicationStatuses',
            'pendingBookingUrls',
            'nextBooking',
            'recentNotifications',
        ));
    }
    private function getRecommendedEvents(array $categoryIds, ?string $search = null)
    {
        $query = Event::with(['organizer.organizerProfile', 'eventType', 'categories', 'photos'])
            ->whereIn('status', ['Open', 'open'])
            ->whereDate('event_date', '>=', now()->toDateString())
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            });
    
            $search = is_string($search) ? trim($search) : null;

            if ($search !== null && $search !== '') {
                $like = '%'.$search.'%';
                $query->where(function ($q) use ($like) {
                    $q->where('title', 'ilike', $like)   // Postgres case-insensitive
                      ->orWhere('venue', 'ilike', $like)
                      ->orWhere('description', 'ilike', $like);
                });
            }
    
        return $query->orderBy('created_at', 'desc')->get();
    }
}
