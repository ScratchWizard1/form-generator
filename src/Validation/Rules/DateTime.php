<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;
use DateTimeImmutable;

class DateTime extends Validation implements ValidationInterface
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
     * Check if the given data is a valid datetime string.
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
        $d = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $data);

        if (!$d) {
            $d = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data);
        }

        if ($d && ($d->format('Y-m-d\TH:i') === $data || $d->format('Y-m-d H:i:s') === $data)) {
            return true;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The value '"
                    . Globals::esc($data)
                    . "' must be a valid datetime in the format YYYY-MM-DD\THH:MM.",
                422,
                "vE018",
                $devMessage
            );
        }
    }
}
