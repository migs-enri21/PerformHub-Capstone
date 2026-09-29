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
            'region' => [$presence, 'string', 'max:150'],
            'city' => [$presence, 'string', 'max:150'],
            'barangay' => ['nullable', 'string', 'max:150'],
            'location_search' => ['nullable', 'string', 'max:255'],
        ];
    }

    public static function profileLocationAttributes(array $validated): array
    {
        $formatted = trim((string) ($validated['location_search'] ?? ''));

        if ($formatted === '') {
            $formatted = self::formatLocation(
                $validated['barangay'] ?? null,
                $validated['city'],
                $validated['region']
            );
        }

        return [
            'region' => $validated['region'],
            'city' => $validated['city'],
            'barangay' => $validated['barangay'] ?? null,
            'location' => $formatted,
        ];
    }
}
