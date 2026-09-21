<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait ManagesAdminListOptions
{
    /**
     * @return array<int, string>
     */
    public static function activeNames(): array
    {
        return self::withOtherLast(
            static::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name')
                ->all()
        );
    }

    /**
     * Keep A–Z order, but always pin "Other" at the end of dropdowns.
     *
     * @param  array<int, string>  $names
     * @return array<int, string>
     */
    public static function withOtherLast(array $names): array
    {
        $other = [];
        $rest = [];

        foreach ($names as $name) {
            if (is_string($name) && strcasecmp($name, 'Other') === 0) {
                $other[] = $name;
            } else {
                $rest[] = $name;
            }
        }

        return [...$rest, ...$other];
    }

    public static function makeSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $counter = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
