<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Portfolio;
use App\Services\PerformerRecommendationService;
use App\Support\PortfolioFeed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(PerformerRecommendationService $recommendations): View
    {
        return view('organizer.dashboard', $this->getDashboardData($recommendations));
    }

    private function getDashboardData(PerformerRecommendationService $recommendations): array
    {
        $overviewData = $this->getOverviewData();
        $recommendationEvent = $overviewData['upcomingEvents']->first();
        $recommendedPerformers = collect();

        if ($recommendationEvent) {
            $recommendedPerformers = $recommendations->forEvent($recommendationEvent);
        }

        return array_merge($overviewData, [
            'recentNotifications' => $this->getRecentNotifications(),
            'recommendationEvent' => $recommendationEvent,
            'recommendedPerformers' => $recommendedPerformers,
            'feedPosts' => $this->getFeedPosts($recommendedPerformers),
        ]);
    }

    private function getOverviewData(): array
    {
        Event::markPastEventsEnded();

        $upcomingEvents = Event::where('organizer_id', Auth::id())
            ->where('status', 'Open')
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->take(3)
            ->get();

        return [
            'upcomingEvents' => $upcomingEvents,
            'pendingBookings' => Booking::where('organizer_id', Auth::id())
                ->where('status', 'pending')
                ->count(),
            'activeBookings' => Booking::where('organizer_id', Auth::id())
                ->where('status', 'accepted')
                ->count(),
        ];
    }

    private function getRecentNotifications(): Collection
    {
        return Auth::user()->notifications()->latest()->take(3)->get();
    }

    private function getFeedPosts(Collection $recommendedPerformers): Collection
    {
        $eventPosts = Event::with(['organizer.organizerProfile', 'photos'])
            ->where('organizer_id', Auth::id())
            ->latest()
            ->take(10)
            ->get()
            ->map(function (Event $event) {
                return [
                    'type' => 'event',
                    'created_at' => $event->created_at,
                    'event' => $event,
                ];
            });

        $recommendedPerformerIds = $recommendedPerformers->pluck('id');
        $portfolioPosts = collect();

        if ($recommendedPerformerIds->isNotEmpty()) {
            $portfolioPosts = PortfolioFeed::groupItems(
                Portfolio::with(['performerProfile.user', 'performerProfile.categories'])
                    ->whereIn('performer_profile_id', $recommendedPerformerIds)
                    ->latest()
                    ->take(50)
                    ->get()
            )
                ->take(10)
                ->map(function (Collection $items) {
                    return [
                        'type' => 'portfolio',
                        'created_at' => $items->first()->created_at,
                        'items' => $items,
                        'performer' => $items->first()->performerProfile,
                    ];
                });
        }

        return $eventPosts
            ->concat($portfolioPosts)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();
    }
}
