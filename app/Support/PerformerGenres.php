<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class PerformerGenres
{
    public static function all(): array
    {
        return config('genres.options', []);
    }

    public static function validationRule(bool $required = false): array
    {
        $rules = $required
            ? ['required', 'string', 'max:100']
            : ['nullable', 'string', 'max:100'];

        $rules[] = Rule::in(self::all());

        return $rules;
    }
}
