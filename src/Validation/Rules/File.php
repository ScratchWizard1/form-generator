<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Validation;
use FormGenerator\Config\Globals;
use FormGenerator\Validation\ValidationInterface;

class File extends Validation implements ValidationInterface
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
     * Validate that the uploaded file meets the required conditions.
     *
     * @param string|array<mixed> $data
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
        if (is_string($data)) {
            return Globals::INTERNAL_EMPTY;
        }

        $requiredKeys = ['name', 'type', 'tmp_name', 'error', 'size'];
        $isFileArray = empty(array_diff($requiredKeys, array_keys($data)));
        if (!$isFileArray) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: test:'"
                    . Globals::esc($test['test']) . "'is used exclusively for file input"
                    . " validation and must be provided as an array containing file data.",
                422,
                "vE055",
                $devMessage
            );
        }

        /**
         * @var array{name: string, type: string, tmp_name: string, error: int, size: int} $data
         */

        if ($data['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $data['error'];
            return FormErrorHandler::exceptionOrFalse(
                "An error occurred during file upload: "
                    . "Validation error: file upload failed with error code "
                    . Globals::esc($errorCode)
                    . ". File might not be uploaded via HTTP POST.",
                422,
                "vE056",
                $devMessage
            );
        }

        if (!is_uploaded_file($data['tmp_name'])) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: file is not a real uploaded file."
                    . " Check 'tmp_name' and ensure it is uploaded via HTTP POST.",
                422,
                "vE057",
                $devMessage
            );
        }
        return true;
    }
}
