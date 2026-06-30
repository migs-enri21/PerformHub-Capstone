<?php

namespace App\Services;

use App\Models\PerformerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PerformerRecommendationService
{
    public function recommend(?int $categoryId = null, ?string $location = null, int $limit = 6): Collection
    {
        $query = PerformerProfile::query()
            ->with(['user', 'category'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true)->where('is_verified', true));

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($location) {
            $query->where('location', 'like', '%'.$location.'%');
        }

        return $query
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function forOrganizer(User $organizer, int $limit = 6): Collection
    {
        $profile = $organizer->organizerProfile;

        return $this->recommend(
            categoryId: null,
            location: $profile?->location,
            limit: $limit
        );
    }
}
