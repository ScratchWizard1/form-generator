<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Regex extends Validation implements ValidationInterface
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
     * Validate that the provided string matches the specified regex pattern.
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
                "vE108",
                $devMessage
            );
        }

        if ($test['value'][0] == Globals::REGEX_REPLACE_VALUE) {
            $value = Globals::getRegex();
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: Invalid regex value",
                400,
                "vE109",
                $devMessage
            );
        }

        if (!preg_match('/^(.).+\1[imsxuADSUXJu]*$/', $value)) {
            $value = '/' . trim($value, '/') . '/';
        }

        if (@preg_match($value, '') === false) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The value is not a valid regex.",
                422,
                "vE110",
                $devMessage
            );
        }
        Globals::setRegex($value);
        if (preg_match($value, $data)) {
            return true;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The value '"
                    . Globals::esc($data)
                    . "' does not match the required pattern. Allowed pattern: '"
                    . Globals::esc($value) . "'.",
                422,
                "vE111",
                $devMessage
            );
        }
    }
}
