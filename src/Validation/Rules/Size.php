<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;
use FormGenerator\Config\Globals;

class Size extends Validation implements ValidationInterface
{
    private const UNITS = ['B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4];

    /**
     * Private constructor to prevent instantiation.
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        ; // This constructor is private and empty; no code is executed here.
    }

    /**
     * Validate that the uploaded file's size does not exceed the specified limit.
     *
     * @param string|array{name: string, type: string, tmp_name: string, error: int, size: int} $data
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return bool|string
     */
    public static function check(
        string|array $data,
        array $test,
        bool $devMessage
    ): bool|string {
        $file = self::isValidFile($data, 'file', $test, false);
        if (!$file->isValid()) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: test:'" .
                    Globals::esc($test['test']) . "' is used exclusively for file input validation"
                    . " and must be provided as an array containing file data.",
                422,
                "vE049",
                $devMessage
            );
        }

        /**
         * @var array{name: string, type: string, tmp_name: string, error: int, size: int} $data
         */

        if (!isset($test['value'][0])) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: Missing value for '"
                    . Globals::esc($test['test']) . "'",
                400,
                "vE050",
                $devMessage
            );
        }

        $sizeStr = strtoupper(trim($test['value'][0]));
        if (!preg_match('/^(\d+(?:[.,]\d+)?)\s*([KMGT]?B)?$/i', $sizeStr, $matches)) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: size format is invalid for test value '"
                    . Globals::esc($sizeStr) . "'.",
                422,
                "vE051",
                $devMessage
            );
        }

        $number = (float) str_replace(',', '.', $matches[1]);
        $unit = $matches[2] ?? 'MB';
        $unit = strtoupper($unit);

        if (!isset(self::UNITS[$unit])) {
            return FormErrorHandler::exceptionOrFalse(
                'Validation error: unknown size unit "'
                    . Globals::esc($unit) . '", only "B","KB","MB","GB","TB" are allowed.',
                422,
                "vE052",
                $devMessage
            );
        }

        $maxBytes = $number * (1024 ** self::UNITS[$unit]);

        if ($data['size'] <= $maxBytes) {
            return true;
        }

        $filename = htmlspecialchars($data['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return FormErrorHandler::exceptionOrFalse(
            "Validation error: uploaded '"
                . Globals::esc($filename) . "' has an invalid size. Max allowed is "
                . Globals::esc($number) . " "
                . Globals::esc($unit),
            422,
            "vE053",
            $devMessage
        );
    }
}
