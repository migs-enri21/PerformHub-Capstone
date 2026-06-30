<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class PhilippineLocations
{
    public static function places(): array
    {
        return config('locations.places', []);
    }

    public static function regions(): array
    {
        return array_keys(self::places());
    }

    public static function cities(string $region): array
    {
        return array_keys(self::places()[$region] ?? []);
    }

    public static function barangays(string $region, string $city): array
    {
        return self::places()[$region][$city] ?? [];
    }

    public static function hasCity(string $region, string $city): bool
    {
        return in_array($city, self::cities($region), true);
    }

    public static function hasBarangay(string $region, string $city, string $barangay): bool
    {
        return in_array($barangay, self::barangays($region, $city), true);
    }

    public static function formatLocation(string $barangay, string $city, string $region): string
    {
        return "{$barangay}, {$city}, {$region}";
    }

    public static function locationFieldsRules(bool $required = true): array
    {
        $presence = $required ? 'required' : 'nullable';

        return [
            'region' => [$presence, 'string', 'max:100', Rule::in(self::regions())],
            'city' => [
                $presence,
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $region = request()->input('region');

                    if (! $value || ! $region || self::hasCity($region, (string) $value)) {
                        return;
                    }

                    $fail('Please select a valid city for the chosen region.');
                },
            ],
            'barangay' => [
                $presence,
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $region = request()->input('region');
                    $city = request()->input('city');

                    if (! $value || ! $region || ! $city || self::hasBarangay($region, (string) $city, (string) $value)) {
                        return;
                    }

                    $fail('Please select a valid barangay for the chosen city.');
                },
            ],
        ];
    }

    public static function profileLocationAttributes(array $validated): array
    {
        return [
            'region' => $validated['region'],
            'city' => $validated['city'],
            'barangay' => $validated['barangay'],
            'location' => self::formatLocation($validated['barangay'], $validated['city'], $validated['region']),
        ];
    }
}
