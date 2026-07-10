<?php

namespace App\Support;

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
}
