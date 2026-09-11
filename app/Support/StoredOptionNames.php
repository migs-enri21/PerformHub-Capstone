<?php

namespace App\Support;

use App\Models\Event;
use App\Models\PerformerProfile;

class StoredOptionNames
{
    public static function renameOnPerformers(string $column, string $from, string $to): void
    {
        if ($from === $to || $from === '' || $to === '') {
            return;
        }

        PerformerProfile::query()
            ->whereJsonContains($column, $from)
            ->each(function (PerformerProfile $profile) use ($column, $from, $to) {
                $profile->update([
                    $column => self::replace($profile->{$column}, $from, $to),
                ]);
            });
    }

    public static function renameOnEvents(string $from, string $to): void
    {
        if ($from === $to || $from === '' || $to === '') {
            return;
        }

        Event::query()
            ->whereJsonContains('preferred_genres', $from)
            ->each(function (Event $event) use ($from, $to) {
                $event->update([
                    'preferred_genres' => self::replace($event->preferred_genres, $from, $to),
                ]);
            });
    }

    /**
     * @return array<int, string>
     */
    private static function replace(mixed $value, string $from, string $to): array
    {
        return array_values(array_unique(array_map(
            fn ($item) => $item === $from ? $to : $item,
            OptionList::wrap($value)
        )));
    }
}
