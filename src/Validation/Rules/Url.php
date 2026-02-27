<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Url extends Validation implements ValidationInterface
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
     * Validate that the provided string is a valid URL.
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
        if (!filter_var($data, FILTER_VALIDATE_URL)) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The value '"
                    . Globals::esc($data) . "' must be a valid url address.",
                422,
                "vE119",
                $devMessage
            );
        }

        if (isset($test['modification'])) {
            if ($test['modification'] != "exist") {
                return FormErrorHandler::exceptionOrFalse(
                    "Validation error: Unknown modifier '"
                        . Globals::esc($test['modification']) . "' — the test 'isValid"
                        . Globals::esc($test['test']) . "' does not support this modification. ",
                    400,
                    "vE120",
                    $devMessage
                );
            }

            $ch = curl_init($data);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_USERAGENT => 'URL-Connectivity-Check/1.0',
                CURLOPT_RANGE => '0-0',
            ]);

            $response = curl_exec($ch);

            $errNo = curl_errno($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);


            if (
                $errNo === 0
                && $httpCode >= 200
                && $httpCode < 400
                && is_string($contentType)
                && stripos($contentType, 'text/html') !== false
                && is_string($response)
                && trim($response) !== ''
            ) {
                return true;
            } else {
                return FormErrorHandler::exceptionOrFalse(
                    'Validation error: The URL address "'
                        . Globals::esc($data)
                        . '" is unavailable.',
                    422,
                    "vE121",
                    $devMessage
                );
            }
        }

        return true;
    }
}
