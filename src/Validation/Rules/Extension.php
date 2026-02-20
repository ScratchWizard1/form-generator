<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;
use FormGenerator\Config\Globals;

class Extension extends Validation implements ValidationInterface
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
     * Validate that the uploaded file has an allowed extension.
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
        bool $devMessage,
        array $backtrace = [8, 7]
    ): bool|string {
        $strict = false;
        if (!isset($test['value'])) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: Missing value for '"
                    . Globals::esc($test['test']) . "'",
                400,
                "vE039",
                $devMessage,
                $backtrace
            );
        }
        if (isset($test['modification'])) {
            if ($test['modification'] != "strict") {
                return FormErrorHandler::exceptionOrFalse(
                    "Validation error: Unknown modifier '"
                        . Globals::esc($test['modification']) . "' - the test 'isValid"
                        . Globals::esc($test['test']) . "' does not support this modification. ",
                    400,
                    "vE040",
                    $devMessage,
                    $backtrace
                );
            }
            $strict = true;
        }
        $file = self::isValidFile($data, 'file', $test, false);
        if (!$file->isValid()) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: test:'"
                    . Globals::esc($test['test'])
                    . "'is used exclusively for file input validation "
                    . "and must be provided as an array containing file data.",
                422,
                "vE041",
                $devMessage,
                $backtrace
            );
        }

        /**
         *  @var array{name: string, type: string, tmp_name: string, error: int, size: int} $data
         */

        $fileName = $data['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = array_map(function ($ext) {
            return strtolower(ltrim($ext, '.'));
        }, $test['value']);
        if (!$strict) {
            $allowedExtensions = self::normalizedExtension($allowedExtensions);
        }
        if (in_array($fileExt, $allowedExtensions)) {
            return true;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: uploaded '"
                    . Globals::esc($data['name']) . "' has an invalid extension '"
                    . Globals::esc($fileExt) . "'. Allowed extensions: "
                    . Globals::esc(implode(', ', $allowedExtensions)) . ".",
                422,
                "vE042",
                $devMessage,
                $backtrace
            );
        }
    }

    /**
     * Normalize extensions by including common variants.
     *
     * @param array<string> $array
     * @return array<string>
     */
    private static function normalizedExtension(array $array): array
    {
        $result = $array;
        if (count(array_intersect(['jpg', 'jpeg'], $result)) > 0) {
            $result = array_unique(array_merge($result, ['jpg', 'jpeg']));
        }

        if (count(array_intersect(['svg', 'svgz'], $result)) > 0) {
            $result = array_unique(array_merge($result, ['svg', 'svgz']));
        }

        if (count(array_intersect(['doc', 'dot'], $result)) > 0) {
            $result = array_unique(array_merge($result, ['doc', 'dot']));
        }

        if (count(array_intersect(['docx', 'dotx'], $result)) > 0) {
            $result = array_unique(array_merge($result, ['docx', 'dotx']));
        }

        if (count(array_intersect(['xls', 'xlt'], $result)) > 0) {
            $result = array_unique(array_merge($result, ['xls', 'xlt']));
        }
        if (count(array_intersect(['xlsx', 'xltx'], $result)) > 0) {
            $result = array_unique(array_merge($result, ['xlsx', 'xltx']));
        }

        if (count(array_intersect(['ppt', 'pot', 'pps'], $result)) > 0) {
            $result = array_unique(array_merge($result, ['ppt', 'pot', 'pps']));
        }
        if (count(array_intersect(['pptx', 'potx', 'ppsx'], $result)) > 0) {
            $result = array_unique(array_merge($result, ['pptx', 'potx', 'ppsx']));
        }
        return $result;
    }
}
