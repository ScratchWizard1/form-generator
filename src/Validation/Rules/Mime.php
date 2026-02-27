<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;
use FormGenerator\Config\Globals;
use finfo;

class Mime extends Validation implements ValidationInterface
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
     * Validate that the uploaded file has an allowed MIME type.
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
                "Validation error: test:'"
                    . Globals::esc($test['test']) . "'is used exclusively for file input validation"
                    . " and must be provided as an array containing file data.",
                422,
                "vE044",
                $devMessage
            );
        }

        /**
         * @var array{name: string, type: string, tmp_name: string, error: int, size: int} $data
         */

        if (!isset($test['value'])) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: Missing value for '"
                    . Globals::esc($test['test']) . "'",
                400,
                "vE045",
                $devMessage
            );
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($data['tmp_name']);

        if ($mimeType === false) {
            return FormErrorHandler::exceptionOrFalse(
                "Unable to determine MIME type of uploaded file.",
                422,
                "vE046",
                $devMessage
            );
        }

        $allowedMimeTypes = array_map('strtolower', $test['value']);

        if (in_array(strtolower($mimeType), $allowedMimeTypes)) {
            return true;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: uploaded '"
                    . Globals::esc($data['name']) . "' has an invalid MIME type '"
                    . Globals::esc($mimeType) . "'. Allowed types: "
                    . Globals::esc(implode(', ', $allowedMimeTypes)) . ".",
                422,
                "vE047",
                $devMessage
            );
        }
    }
}
