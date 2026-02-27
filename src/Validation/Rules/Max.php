<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use DateTimeImmutable;
use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Max extends Validation implements ValidationInterface
{
    /**
     * Private constructor to prevent instantiation.
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        ; // This constructor is private and empty; no code is executed here.
    }

    /**
     * Validate that the provided string does not exceed the specified maximum value.
     *
     * @param string $data
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return bool|string
     */
    public static function check(
        string $data,
        array $test,
        bool $devMessage
    ): bool|string {
        if (trim($data) == '' || $data == Globals::INTERNAL_EMPTY) {
            return Globals::INTERNAL_EMPTY;
        }
        if (!isset($test['value'][0])) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: Missing value for '"
                    . Globals::esc($test['test']) . "'",
                400,
                "vE071",
                $devMessage
            );
        }
        $value = $test['value'][0];
        $type1 = self::detectType($data);
        $type2 = self::detectType($value);
        if ($type1 == "unknown" || $type2 == "unknown") {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The provided values '"
                    . Globals::esc($data) . "' or '"
                    . Globals::esc($value) . "' have an unknown type.",
                422,
                "vE072",
                $devMessage
            );
        }

        if (!self::areComparable($type1, $type2)) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The provided values '"
                    . Globals::esc($data) . "' and '"
                    . Globals::esc($value) . "' are not comparable.",
                422,
                "vE073",
                $devMessage
            );
        }
        $val1 = self::convertToComparable($data, $type1);
        $val2 = self::convertToComparable($value, $type2);
        if ($val1 <= $val2) {
            return true;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The value '"
                    . Globals::esc($data) . "' exceeds the allowed maximum of '"
                    . Globals::esc($value) . "'.",
                422,
                "vE074",
                $devMessage
            );
        }
    }

    /**
     * Detect the type of the given value.
     *
     * @param string $value
     * @return string
     */
    private static function detectType(string $value): string
    {
        $isTimestamp = function ($val) {
            return ctype_digit($val) && (int)$val >= 0 && (int)$val <= PHP_INT_MAX;
        };


        if ($isTimestamp($value)) {
            return 'timestamp';
        }

        if (is_numeric($value)) {
            return 'number';
        }

        $isValidDate = function (string $format, string $val) {
            $dt = DateTimeImmutable::createFromFormat($format, $val);
            return $dt && $dt->format($format) === $val;
        };

        if ($isValidDate('Y-m-d\TH:i:sP', $value)) {
            return 'datetime_tz';
        }
        if ($isValidDate('Y-m-d\TH:i:s', $value)) {
            return 'datetime_sec';
        }
        if ($isValidDate('Y-m-d\TH:i', $value)) {
            return 'datetime';
        }

        if ($isValidDate('Y-m-d', $value)) {
            return 'date';
        }
        if ($isValidDate('H:i:s', $value)) {
            return 'time_sec';
        }
        if ($isValidDate('H:i', $value)) {
            return 'time';
        }

        if (preg_match('/^\d{4}-W([0-4][0-9]|5[0-3])$/', $value)) {
            return 'week';
        }

        if ($isValidDate('Y-m', $value)) {
            return 'year_month';
        }

        if ($isValidDate('Y', $value)) {
            return 'year';
        }
        return 'unknown';
    }

    /**
     * Check if two types are comparable.
     *
     * @param string $type1
     * @param string $type2
     * @return bool
     */
    private static function areComparable(string $type1, string $type2): bool
    {
        $groups = [
            'number' => [
                'number',
                'timestamp'
            ],
            'timestamp' => [
                'timestamp',
                'date',
                'datetime',
                'datetime_sec',
                'datetime_tz',
                'week',
                'year_month',
                'year'
            ],
            'date' => [
                'date',
                'datetime',
                'datetime_sec',
                'datetime_tz',
                'week',
                'year_month',
                'year',
                'timestamp'
            ],
            'time' => [
                'time',
                'time_sec'
            ]
        ];

        foreach ($groups as $group) {
            if (in_array($type1, $group, true) && in_array($type2, $group, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert the given value to a comparable format based on its type.
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private static function convertToComparable($value, string $type): mixed
    {
        switch ($type) {
            case 'number':
                return floatval($value);
            case 'timestamp':
                return (int)$value;
            case 'date':
            case 'datetime':
            case 'datetime_sec':
            case 'datetime_tz':
            case 'week':
            case 'year_month':
            case 'year':
                $dt = new DateTimeImmutable($value);
                return $dt->getTimestamp();
            case 'time':
            case 'time_sec':
                $dt = new DateTimeImmutable('1970-01-01 ' . $value);
                return $dt->getTimestamp();
            default:
                return null;
        }
    }
}
