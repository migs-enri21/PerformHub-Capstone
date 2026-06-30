<?php

namespace App\Models\Concerns;

trait HasPhilippineLocation
{
    public function fullLocation(): string
    {
        if ($this->barangay && $this->city && $this->region) {
            return "{$this->barangay}, {$this->city}, {$this->region}";
        }

        return $this->location ?? '';
    }

    public function locationParts(): array
    {
        return [
            'region' => $this->region ?? '',
            'city' => $this->city ?? '',
            'barangay' => $this->barangay ?? '',
        ];
    }
}
