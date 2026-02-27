<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Ip extends Validation implements ValidationInterface
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
     * Validate that the provided string is a valid IP address.
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
        if (isset($test['modification'])) {
            if ($test['modification'] != "v6") {
                return FormErrorHandler::exceptionOrFalse(
                    "Validation error: Unknown modifier '"
                        . Globals::esc($test['modification']) . "' — the test 'isValid"
                        . Globals::esc($test['test']) . "' does not support this modification. ",
                    400,
                    "vE059",
                    $devMessage
                );
            }

            if (filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return true;
            } else {
                return FormErrorHandler::exceptionOrFalse(
                    "Validation error: The string '"
                        . Globals::esc($data) . "' is not a valid IPv6 address.",
                    422,
                    "vE060",
                    $devMessage
                );
            }
        }
        if (filter_var($data, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The string '"
                    . Globals::esc($data) . "' is not a valid IPv4 address.",
                422,
                "vE061",
                $devMessage
            );
        }
    }
}
