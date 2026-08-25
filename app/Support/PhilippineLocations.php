<?php

namespace App\Support;

class PhilippineLocations
{
    public static function formatLocation(?string $barangay, string $city, string $region): string
    {
        return implode(', ', array_filter([$barangay, $city, $region]));
    }

    public static function locationFieldsRules(bool $required = true): array
    {
        $presence = $required ? 'required' : 'nullable';

        return [
            // Region/city now come from Google's address lookup rather than a
            // fixed local list, so they're just validated as plain strings.
            'region' => [$presence, 'string', 'max:150'],
            'city' => [$presence, 'string', 'max:150'],
            // Not every address Google resolves includes a barangay-level
            // component, so it stays optional regardless of $required.
            'barangay' => ['nullable', 'string', 'max:150'],
        ];
    }

    public static function profileLocationAttributes(array $validated): array
    {
        return [
            'region' => $validated['region'],
            'city' => $validated['city'],
            'barangay' => $validated['barangay'] ?? null,
            'location' => self::formatLocation($validated['barangay'] ?? null, $validated['city'], $validated['region']),
        ];
    }
}
