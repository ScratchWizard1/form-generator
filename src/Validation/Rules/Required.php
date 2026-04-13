<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Required extends Validation implements ValidationInterface
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
     * Validate that the provided data is present and not empty.
     *
     * @param array<mixed>|string $data
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @return bool
     */
    public static function check(
        array|string $data,
        array $test,
        bool $devMessage
    ): bool {
        if (empty($data)) {
            return false;
        }
        if (is_string($data)) {
            if (
                trim($data) == '' ||
                $data == Globals::INTERNAL_EMPTY
            ) {
                return false;
            }
        }
        return true;
    }
}
