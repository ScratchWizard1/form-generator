<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use DateTimeImmutable;
use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Week extends Validation implements ValidationInterface
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
     * Validate that the provided string is a valid week.
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

        if (preg_match('/^(?:19|20)\d{2}-W(?:0[1-9]|[1-4][0-9]|5[0-3])$/', $data)) {
            return true;
        }

        return FormErrorHandler::exceptionOrFalse(
            "Validation error: The value '"
                . Globals::esc($data) . "' is not a valid week.",
            422,
            "vE123",
            $devMessage
        );
    }
}
