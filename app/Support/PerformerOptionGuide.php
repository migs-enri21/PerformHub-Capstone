<?php

namespace App\Support;

use App\Models\Genre;
use Illuminate\Support\Collection;

class PerformerOptionGuide
{
    /**
     * Category id => name and admin genres assigned to that category.
     *
     * @param  Collection<int, \App\Models\Category>|iterable  $categories
     * @return array<string, array{name: string, genres: array<int, string>}>
     */
    public static function catalog(iterable $categories): array
    {
        $categories = collect($categories);
        $ids = $categories->pluck('id')->filter()->all();

        $genresByCategory = Genre::query()
            ->where('is_active', true)
            ->whereIn('category_id', $ids)
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($genre) => (string) $genre->category_id);

        $catalog = [];

        foreach ($categories as $category) {
            $catalog[(string) $category->id] = [
                'name' => $category->name,
                'genres' => Genre::withOtherLast(
                    $genresByCategory->get((string) $category->id, collect())->pluck('name')->all()
                ),
            ];
        }

        return $catalog;
    }

    /**
     * @param  array<string, array{name: string, genres: array<int, string>}>  $catalog
     * @param  array<int, int|string>  $categoryIds
     * @return array<int, array{label: string, options: array<int, string>}>
     */
    public static function groupsForIds(array $catalog, array $categoryIds, string $kind = 'genres'): array
    {
        $groups = [];

        foreach ($categoryIds as $id) {
            $entry = $catalog[(string) $id] ?? null;

            if (! $entry) {
                continue;
            }

            $options = $entry[$kind] ?? [];

            if ($options === []) {
                continue;
            }

            $groups[] = [
                'label' => $entry['name'],
                'options' => $options,
            ];
        }

        return $groups;
    }

    /**
     * @param  Collection<int, \App\Models\Category>|iterable  $categories
     * @param  array<int, string>  $extraAllowed
     * @return array<int, string>
     */
    public static function allowedNames(iterable $categories, array $extraAllowed = []): array
    {
        $names = [];

        foreach (self::catalog($categories) as $entry) {
            foreach ($entry['genres'] ?? [] as $name) {
                $names[] = $name;
            }
        }

        return array_values(array_unique([...$names, ...array_filter($extraAllowed, fn ($item) => is_string($item) && $item !== '')]));
    }
}
