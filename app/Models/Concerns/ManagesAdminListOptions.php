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
        return static::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->all();
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
