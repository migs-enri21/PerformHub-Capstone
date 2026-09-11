<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class PerformerSpecialties
{
    public static function all(): array
    {
        return config('specialties.options', []);
    }

    public static function validationRule(?string $current = null): array
    {
        $allowed = self::all();

        if ($current && ! in_array($current, $allowed, true)) {
            $allowed[] = $current;
        }

        return ['nullable', 'string', 'max:100', Rule::in($allowed)];
    }

    /**
     * @param  array<int, string>  $extraAllowed
     * @return array<int, mixed>
     */
    public static function listItemRule(array $extraAllowed = []): array
    {
        return ['string', 'max:100', Rule::in(array_values(array_unique([...self::all(), ...$extraAllowed])))];
    }
}
