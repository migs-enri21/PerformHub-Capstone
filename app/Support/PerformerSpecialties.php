<?php

namespace App\Support;

use App\Models\Specialty;
use Illuminate\Validation\Rule;

class PerformerSpecialties
{
    public static function all(): array
    {
        return Specialty::activeNames();
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
