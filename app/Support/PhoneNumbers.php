<?php

namespace App\Support;

class PhoneNumbers
{
    /**
     * Philippine mobile numbers: 09XXXXXXXXX, +639XXXXXXXXX, or 639XXXXXXXXX
     * (spaces/dashes allowed between digit groups).
     */
    public const PATTERN = '/^(\+63|0|63)?[\s\-]*9\d{2}[\s\-]*\d{3}[\s\-]*\d{4}$/';

    public static function rules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:30',
            'regex:'.self::PATTERN,
        ];
    }

    public static function message(): string
    {
        return 'Enter a valid Philippine mobile number (e.g. 09XX XXX XXXX or +63 9XX XXX XXXX).';
    }
}
