<?php

namespace App\Support;

/**
 * Normalizes the Ethiopian mobile formats accepted by the application into a
 * single canonical, local format: 09XXXXXXXX or 07XXXXXXXX.
 */
final class EthiopianPhone
{
    private const LOCAL_PATTERN = '/^0(?:9|7)\d{8}$/';

    private const INTERNATIONAL_PATTERN = '/^(?:\+251|251)(9\d{8}|7\d{8})$/';

    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $phone = preg_replace('/[\s().-]/', '', trim((string) $value));

        if ($phone === null || $phone === '') {
            return null;
        }

        if (preg_match(self::INTERNATIONAL_PATTERN, $phone, $matches) === 1) {
            return '0'.$matches[1];
        }

        if (preg_match(self::LOCAL_PATTERN, $phone) === 1) {
            return $phone;
        }

        return null;
    }

    public static function isValid(mixed $value): bool
    {
        return self::normalize($value) !== null;
    }
}
