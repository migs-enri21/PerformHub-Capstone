<?php

namespace App\Services;

use App\Models\Event;
use App\Models\PerformerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PerformerRecommendationService
{
    public function forEvent(Event $event, int $limit = 3): Collection
    {
        $categoryIds = $event->categories()->pluck('categories.id');

        if ($categoryIds->isEmpty()) {
            return new Collection();
        }

        $performers = PerformerProfile::with(['user', 'categories', 'portfolios'])
            ->whereHas('user', function ($query) {
                $query->where('is_active', true)
                    ->where('is_verified', true)
                    ->where('onboarding_step', '>=', User::ONBOARDING_COMPLETE);
            })
            ->where('is_verified_badge', true)
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->orderBy('stage_name')
            ->get();

        $preferredGenres = $event->preferred_genres;

        if (empty($preferredGenres)) {
            return $performers->take($limit);
        }

        $genreMatches = new Collection();
        $otherMatches = new Collection();

        foreach ($performers as $performer) {
            if (in_array($performer->genre, $preferredGenres, true)) {
                $genreMatches->push($performer);
            } else {
                $otherMatches->push($performer);
            }
        }

        return $genreMatches->concat($otherMatches)->take($limit);
    }
}
