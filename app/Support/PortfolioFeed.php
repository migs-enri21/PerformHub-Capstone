<?php

namespace App\Support;

use App\Models\Portfolio;
use Illuminate\Support\Collection;

class PortfolioFeed
{
    public static function groupKey(object $item): string
    {
        return $item->performer_profile_id.'|'.($item->caption ?? '__empty__').'|'.$item->created_at->format('Y-m-d H:i');
    }

    public static function groupItems(Collection $items): Collection
    {
        return $items
            ->groupBy(fn ($item) => self::groupKey($item))
            ->map(fn ($group) => $group->values())
            ->sortByDesc(fn ($group) => $group->first()->created_at)
            ->values();
    }

    public static function recentPosts(int $limit = 12): Collection
    {
        $portfolios = Portfolio::query()
            ->with(['performerProfile.user', 'performerProfile.category'])
            ->whereHas('performerProfile.user', fn ($query) => $query->where('is_active', true))
            ->latest()
            ->limit($limit * 6)
            ->get();

        return self::groupItems($portfolios)->take($limit);
    }
}
