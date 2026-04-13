<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Email extends Validation implements ValidationInterface
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
     * Check if the given data is a valid email address.
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
            if ($test['modification'] != "domain") {
                return FormErrorHandler::exceptionOrFalse(
                    "Validation error: Unknown modifier '"
                        . Globals::esc($test['modification']) . "' — the test 'isValid"
                        . Globals::esc($test['test']) . "' does not support this modification. ",
                    400,
                    "vE020",
                    $devMessage
                );
            }
            $pos = strrchr($data, "@");

            if ($pos === false) {
                return FormErrorHandler::exceptionOrFalse(
                    "Validation error: The email address '"
                        . Globals::esc($data) . "' is invalid.",
                    422,
                    "vE021",
                    $devMessage
                );
            }

            $domain = substr($pos, 1);

            $records = @dns_get_record($domain, DNS_MX);
            if ($records) {
                return true;
            } else {
                return FormErrorHandler::exceptionOrFalse(
                    'Validation error: The email domain "'
                        . Globals::esc($domain) . '" does not exist.',
                    422,
                    "vE022",
                    $devMessage
                );
            }
        }
        if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The value '"
                    . Globals::esc($data) . "' must be a valid email address.",
                422,
                "vE023",
                $devMessage
            );
        }
        return true;
    }
}
