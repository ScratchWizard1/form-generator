<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Number extends Validation implements ValidationInterface
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
     * Validate that the provided string is a valid number.
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
        $data = trim($data);
        $data = str_replace(",", ".", $data);
        if (isset($test['modification'])) {
            $modifications = explode("\x1F", $test['modification']);
            foreach ($modifications as $modification) {
                if (!in_array($modification, ['only', 'float', 'integer'])) {
                    return FormErrorHandler::exceptionOrFalse(
                        "Validation error: Unknown modifier '"
                            . Globals::esc($modification) . "' — the test 'isValid"
                            . Globals::esc($test['test']) . "' does not support this modification. ",
                        400,
                        "vE097",
                        $devMessage
                    );
                }
            }
            if (in_array('only', $modifications)) {
                $number = '';
                $data = trim($data);

                $hasDecimal = false;
                $hasExponent = false;
                $hasNegative = false;

                foreach (str_split($data) as $index => $char) {
                    if ($char === '-' && !$hasNegative) {
                        $hasNegative = true;
                        $number .= '-';
                        continue;
                    }

                    if (
                        ($char === '.' || $char === ',') &&
                        !$hasDecimal &&
                        !$hasExponent
                    ) {
                        $hasDecimal = true;
                        $number .= '.';
                        continue;
                    }

                    if (
                        strtolower($char) === 'e' &&
                        !$hasExponent &&
                        $index > 0 &&
                        $index < strlen($data) - 1
                    ) {
                        $hasExponent = true;
                        $number .= 'e';
                        continue;
                    }

                    if (ctype_digit($char)) {
                        $number .= $char;
                    }
                }
                if (!is_numeric($number)) {
                    return FormErrorHandler::exceptionOrFalse(
                        "Validation error: Failed to convert the value '"
                            . Globals::esc($data) . "' to a number.",
                        422,
                        "vE098",
                        $devMessage
                    );
                }
                $number = trim($number);
                if (!array_intersect($modifications, ['integer', 'float'])) {
                    return  $number;
                }

                if (in_array('float', $modifications)) {
                    $inf = (float)$number;
                    if ($inf === INF || $inf === -INF) {
                        return FormErrorHandler::exceptionOrFalse(
                            "Validation error: The value '"
                                . Globals::esc($data) . "' is too large to be processed as a float.",
                            422,
                            "vE099",
                            $devMessage
                        );
                    }
                    if (
                        strpos($number, '.') === false &&
                        stripos($number, 'e') === false
                    ) {
                        return $number . ".0";
                    }
                }
                if (in_array('integer', $modifications)) {
                    $data = floatval($data);
                    $minInt = PHP_INT_MIN;
                    $maxInt = PHP_INT_MAX;

                    if (
                        $data > $maxInt ||
                        $data < $minInt ||
                        is_infinite($data) ||
                        is_nan($data)
                    ) {
                        return FormErrorHandler::exceptionOrFalse(
                            "Validation error: The value '"
                                . Globals::esc($data) . "' is too large to be processed as an integer.",
                            422,
                            "vE100",
                            $devMessage
                        );
                    }
                    return (string) (int) (float) $number;
                }
            }
            if (in_array('float', $modifications)) {
                if (filter_var($data, FILTER_VALIDATE_FLOAT) === false) {
                    return FormErrorHandler::exceptionOrFalse(
                        "Validation error: The value '"
                            . Globals::esc($data) . "' is not a valid float.",
                        422,
                        "vE101",
                        $devMessage
                    );
                }
                return true;
            }
            if (in_array('integer', $modifications)) {
                if (filter_var($data, FILTER_VALIDATE_INT) === false) {
                    return FormErrorHandler::exceptionOrFalse(
                        "Validation error: The value '"
                            . Globals::esc($data) . "' is not a valid integer.",
                        422,
                        "vE102",
                        $devMessage
                    );
                }
                return true;
            }
        }
        if (is_numeric($data)) {
            return true;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The value '"
                    . Globals::esc($data) . "' is not a valid number.",
                422,
                "vE103",
                $devMessage
            );
        }
    }
}
