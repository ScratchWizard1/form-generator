<?php

declare(strict_types=1);

namespace FormGenerator\Validation;

interface ValidationInterface
{
    /**
     * Validate the provided data according to specific rules.
     *
     * @param string $data
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @return bool|string
     */
    public static function check(
        string $data,
        array $test,
        bool $devMessage
    ): bool|string;
}
