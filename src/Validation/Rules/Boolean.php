<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Boolean extends Validation implements ValidationInterface
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
     * Check if the given data is a valid boolean value.
     *
     * @param string|bool $data
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return bool|string
     */
    public static function check(
        string|bool $data,
        array $test,
        bool $devMessage
    ): bool|string {
        if (is_bool($data)) {
            return true;
        }
        $trimmed = trim($data);
        if ($trimmed == '' || $trimmed == Globals::INTERNAL_EMPTY) {
            return Globals::INTERNAL_EMPTY;
        }
        $value = filter_var(
            $trimmed,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
        if (
            $value !== null
        ) {
            return true;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The value '"
                    . Globals::esc($data) . "' must be a boolean (true or false) only.",
                422,
                "vE012",
                $devMessage
            );
        }
    }
}
