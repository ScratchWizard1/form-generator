<?php

declare(strict_types=1);

namespace FormGenerator\Validation;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Validation\Rules\File;
use FormGenerator\Validation;
use FormGenerator\Config\Globals;
use DateTime;

class Format extends Validation
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
     * Format the given data according to the specified format.
     *
     * @param string|array<mixed> $data
     * @param array{test: string, modification?: string, value?: array<string>}$format
     * @param string $key
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return mixed
     */
    public static function format(
        string|array $data,
        array $format,
        string $key,
        bool $devMessage
    ): mixed {
        if (
            is_string($data) &&
            (trim($data) == '' || $data == Globals::INTERNAL_EMPTY)
        ) {
            return Globals::INTERNAL_EMPTY;
        }
        $file = File::check($data, ['test' => 'isValidFile'], false);
        if (is_array($data) && $file) {
            return $data;
        }
        if (!isset($format['modification'])) {
            return FormErrorHandler::fatalError(
                'Format error: Missing format type. 
                A valid format must be provided after ":" (e.g. format:string).',
                'Format error: Missing format type. 
                A valid format must be provided after ":" (e.g. format:string).',
                422,
                "vE124",
                $devMessage
            );
        }
        if (
            !in_array($format['modification'], [
                'round',
                'integer',
                'float',
                'string',
                'datetime',
                'boolean'
            ])
        ) {
            return FormErrorHandler::fatalError(
                'Unsupported format: "'
                    . Globals::esc($format['modification'])
                    . '". Allowed formats are: round, integer, float, string, datetime, boolean',
                'Unsupported format: "'
                    . Globals::esc($format['modification'])
                    . '". Allowed formats are: round, integer, float, string, datetime, boolean',
                422,
                "vE125",
                $devMessage
            );
        }
        switch ($format['modification']) {
            case 'round':
                if (is_array($data)) {
                    return $data;
                }
                $precision = isset($format['value']) ? (int)$format['value'][0] : 0;
                $float = floatval($data);
                if (
                    $float === INF ||
                    $float === -INF ||
                    is_infinite($float) ||
                    is_nan($float)
                ) {
                    return FormErrorHandler::fatalError(
                        "Validation error: The value '"
                            . Globals::esc($data) . "' is too large to be processed as a float.",
                        "Validation error: The value '"
                            . Globals::esc($data) . "' is too large to be processed as a float.",
                        422,
                        "vE126",
                        $devMessage
                    );
                }
                return round($float, $precision);
            case 'integer':
                if (is_array($data)) {
                    return $data;
                }
                $data = floatval($data);
                $minInt = PHP_INT_MIN;
                $maxInt = PHP_INT_MAX;

                if (
                    $data > $maxInt ||
                    $data < $minInt ||
                    is_infinite($data) ||
                    is_nan($data)
                ) {
                    return FormErrorHandler::fatalError(
                        "Validation error: The value '"
                            . Globals::esc($data) . "' is too large to be processed as an integer.",
                        "Validation error: The value '"
                            . Globals::esc($data) . "' is too large to be processed as an integer.",
                        422,
                        "vE127",
                        $devMessage
                    );
                }
                return (int) (float) $data;
            case 'float':
                if (is_array($data)) {
                    return $data;
                }
                $float = floatval($data);
                if (
                    $float === INF ||
                    $float === -INF ||
                    is_infinite($float) ||
                    is_nan($float)
                ) {
                    return FormErrorHandler::fatalError(
                        "Validation error: The value '"
                            . Globals::esc($data) . "' is too large to be processed as a float.",
                        "Validation error: The value '"
                            . Globals::esc($data) . "' is too large to be processed as a float.",
                        422,
                        "vE128",
                        $devMessage
                    );
                }
                return $float;
            case 'string':
                $file = File::check($data, ['test' => 'isValidFile'], false);
                if (is_array($data) && !$file) {
                    return self::assocArrayToString($data);
                }
                return $data;

            case 'datetime':
                if (is_array($data)) {
                    return $data;
                }
                $value = trim(strtoupper($data));
                $isTimestamp = function ($val) {
                    return ctype_digit($val) && (int)$val >= 0 && (int)$val <= PHP_INT_MAX;
                };

                if ($isTimestamp($value)) {
                    return (new DateTime())->setTimestamp((int)$value);
                }

                $isValidDate = function (string $format, string $val) {
                    $dt = DateTime::createFromFormat($format, $val);
                    return $dt && $dt->format($format) === $val ? $dt : false;
                    ;
                };

                if ($dt = $isValidDate('Y-m-d\TH:i:sP', $value)) {
                    return $dt;
                }
                if ($dt = $isValidDate('Y-m-d\TH:i:s', $value)) {
                    return $dt;
                }
                if ($dt = $isValidDate('Y-m-d\TH:i', $value)) {
                    return $dt;
                }

                if ($dt = $isValidDate('Y-m-d', $value)) {
                    $dt->setTime(0, 0, 0);
                    return $dt;
                }
                if ($dt = $isValidDate('H:i:s', $value)) {
                    $now = new DateTime();
                    $now->setDate(1970, 1, 1);
                    $now->setTime((int)$dt->format('H'), (int)$dt->format('i'), (int)$dt->format('s'));
                    return $now;
                }
                if ($dt = $isValidDate('H:i', $value)) {
                    $now = new DateTime();
                    $now->setDate(1970, 1, 1);
                    $now->setTime((int)$dt->format('H'), (int)$dt->format('i'), 0);
                    return $now;
                }

                if (preg_match('/^(\d{4})-W(\d{2})$/', $value, $m)) {
                    $dt = new DateTime();
                    $dt->setISODate((int)$m[1], (int)$m[2], 1);
                    $dt->setTime(0, 0, 0);
                    return $dt;
                }

                if ($dt = $isValidDate('Y-m', $value)) {
                    $dt->setDate((int)$dt->format('Y'), (int)$dt->format('m'), 1);
                    $dt->setTime(0, 0, 0);
                    return $dt;
                }

                if ($dt = $isValidDate('Y', $value)) {
                    $dt->setDate((int)$dt->format('Y'), 1, 1);
                    $dt->setTime(0, 0, 0);
                }

                return FormErrorHandler::fatalError(
                    "Validation error: The value '"
                        . Globals::esc($data)
                        . "' has an invalid format and cannot be converted to a DateTime object.",
                    "Validation error: The value '"
                        . Globals::esc($data)
                        . "' has an invalid format and cannot be converted to a DateTime object.",
                    422,
                    "vE129",
                    $devMessage
                );
            case 'boolean':
                if (is_array($data)) {
                    return $data;
                }
                $boolean = filter_var(
                    $data,
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );
                if ($boolean === null) {
                    return FormErrorHandler::fatalError(
                        "Validation error: The value '"
                            . Globals::esc($data)
                            . "' has an invalid format and cannot be converted to a boolean.",
                        "Validation error: The value '"
                            . Globals::esc($data)
                            . "' has an invalid format and cannot be converted to a boolean.",
                        422,
                        "vE130",
                        $devMessage
                    );
                }
                return $boolean;
            default:
                return FormErrorHandler::fatalError(
                    "Validation error: The value associated with '"
                        . Globals::esc($key)
                        . "' could not be converted to the expected type due to an invalid or unexpected format.",
                    "Validation error: The value associated with '"
                        . Globals::esc($key)
                        . "' could not be converted to the expected type due to an invalid or unexpected format.",
                    422,
                    "vE131",
                    $devMessage
                );
        }
    }
}
