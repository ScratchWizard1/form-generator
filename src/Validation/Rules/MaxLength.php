<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class MaxLength extends Validation implements ValidationInterface
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
     * Validate that the provided string does not exceed the maximum length.
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
                "vE081",
                $devMessage
            );
        }
        $value = (int) $test['value'][0];
        if (isset($test['modification'])) {
            if ($test['modification'] != "cut") {
                return FormErrorHandler::exceptionOrFalse(
                    "Validation error: Unknown modifier '"
                        . Globals::esc($test['modification']) . "' — the test 'isValid"
                        . Globals::esc($test['test']) . "' does not support this modification. ",
                    400,
                    "vE082",
                    $devMessage
                );
            }
            if (mb_strlen($data) > $value) {
                return substr($data, 0, $value);
            }
        }
        if (mb_strlen($data) <= $value) {
            return true;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The provided string '"
                    . Globals::esc($data) . "' exceeds the allowed maximum length.",
                422,
                "vE083",
                $devMessage
            );
        }
    }
}
