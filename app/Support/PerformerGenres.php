<?php

namespace App\Support;

use App\Models\Genre;
use Illuminate\Validation\Rule;

class PerformerGenres
{
    public static function all(): array
    {
        return Genre::activeNames();
    }

    public static function validationRule(bool $required = false): array
    {
        $rules = $required
            ? ['required', 'string', 'max:100']
            : ['nullable', 'string', 'max:100'];

        $rules[] = Rule::in(self::all());

        return $rules;
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
