<?php

namespace App\Support;

class OptionList
{
    /**
     * @return array<int, string>
     */
    public static function wrap(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($item) => is_string($item) && $item !== ''));
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return self::wrap($decoded);
        }

        return [$value];
    }
}
