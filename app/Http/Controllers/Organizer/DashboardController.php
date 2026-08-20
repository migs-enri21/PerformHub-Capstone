<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Portfolio;
use App\Services\PerformerRecommendationService;
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
            'feedPosts' => $this->getFeedPosts(),
        ]);
    }

    private function getOverviewData(): array
    {
        Event::completePastEvents();

        $upcomingEvents = Event::where('organizer_id', Auth::id())
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

    private function getFeedPosts(): Collection
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

        $portfolioPosts = Portfolio::with(['performerProfile.user', 'performerProfile.categories'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function (Portfolio $portfolio) {
                return [
                    'type' => 'portfolio',
                    'created_at' => $portfolio->created_at,
                    'items' => collect([$portfolio]),
                    'performer' => $portfolio->performerProfile,
                ];
            });

        return $eventPosts
            ->concat($portfolioPosts)
            ->sortByDesc('created_at')
            ->values();
    }
}
