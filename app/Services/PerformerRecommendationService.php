<?php

namespace App\Services;

use App\Models\Event;
use App\Models\PerformerProfile;
use Illuminate\Database\Eloquent\Collection;

class PerformerRecommendationService
{
    public function forEvent(Event $event, int $limit = 3): Collection
    {
        $categoryIds = $event->categories()->pluck('categories.id');

        if ($categoryIds->isEmpty()) {
            return new Collection();
        }

        return PerformerProfile::with(['user', 'categories', 'portfolios'])
            ->whereHas('user', function ($query) {
                $query->where('is_active', true)
                    ->where('is_verified', true);
            })
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->orderBy('stage_name')
            ->limit($limit)
            ->get();
    }
}
