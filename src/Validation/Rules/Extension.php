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
        bool $devMessage
    ): bool|string {
        $strict = false;
        if (!isset($test['value'])) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: Missing value for '"
                    . Globals::esc($test['test']) . "'",
                400,
                "vE039",
                $devMessage
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
                    $devMessage
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
                $devMessage
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
            $allowedExtensions = self::normalizeExtension($allowedExtensions);
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
                $devMessage
            );
        }
    }

    /**
     * Normalize extensions by including common variants.
     *
     * @param array<string> $extensions
     * @return array<string>
     */
    private static function normalizeExtension(array $extensions): array
    {
        $result = array_unique(array_map('strtolower', $extensions));
        $groups = [
            ['jpg', 'jpeg', 'jpe'],
            ['png', 'apng'],
            ['svg', 'svgz'],
            ['tif', 'tiff'],
            ['webp'],
            ['bmp'],
            ['ico', 'cur'],
            ['doc', 'dot'],
            ['xls', 'xlt'],
            ['ppt', 'pot', 'pps'],
            ['docx', 'dotx', 'docm', 'dotm'],
            ['xlsx', 'xltx', 'xlsm', 'xltm'],
            ['pptx', 'potx', 'ppsx', 'pptm', 'potm', 'ppsm'],
            ['odt', 'ott'],
            ['ods', 'ots'],
            ['odp', 'otp'],
            ['odg', 'otg'],
            ['txt', 'text', 'conf', 'cfg'],
            ['md', 'markdown'],
            ['csv'],
            ['tsv'],
            ['htm', 'html'],
            ['xhtml'],
            ['css'],
            ['js', 'mjs', 'cjs'],
            ['mp3', 'mpga'],
            ['m4a'],
            ['ogg', 'oga'],
            ['wav'],
            ['flac'],
            ['mp4', 'm4v'],
            ['avi'],
            ['mkv'],
            ['mov'],
            ['webm'],
            ['wmv'],
            ['zip'],
            ['rar'],
            ['7z'],
            ['tar'],
            ['gz', 'gzip'],
            ['bz2'],
            ['tgz'],
            ['ttf'],
            ['otf'],
            ['woff'],
            ['woff2'],
            ['epub'],
            ['mobi'],
            ['azw', 'azw3'],
        ];

        $lookup = array_flip($result);

        foreach ($groups as $group) {
            foreach ($group as $ext) {
                if (isset($lookup[$ext])) {
                    foreach ($group as $gExt) {
                        $lookup[$gExt] = true;
                    }
                    break;
                }
            }
        }

        return array_keys($lookup);
    }
}
