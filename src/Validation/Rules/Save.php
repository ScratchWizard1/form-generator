<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Config\Config;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Save extends Validation implements ValidationInterface
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
     * Validate and save an uploaded file to the specified directory.
     *
     * @param string|array{name: string, type: string, tmp_name: string, error: int, size: int} $data
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return array{name: string, type: string, tmp_name: string, error: int, size: int}|bool|string
     */
    public static function check(
        string|array $data,
        array $test,
        bool $devMessage
    ): array|bool|string {

        if (empty($data) || $data == Globals::INTERNAL_EMPTY) {
            return Globals::INTERNAL_EMPTY;
        }

        $file = self::isValidFile($data, 'file', $test, false);
        if (!$file->isValid()) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: test:'"
                    . Globals::esc($test['test']) . "'is used exclusively for file input validation"
                    . " and must be provided as an array containing file data.",
                422,
                "vE135",
                $devMessage
            );
        }

        /**
         * @var array{name: string, type: string, tmp_name: string, error: int, size: int} $data
         */
        $savePath = null;

        if (isset($test['value'][0]) && !empty(trim($test['value'][0]))) {
            $savePath = trim($test['value'][0]);
        } else {
            $envPath = Config::getWithFallback(['FG_UPLOAD_PATH', 'UPLOAD_PATH']);
            if (is_string($envPath) && !empty(trim($envPath))) {
                $savePath = trim($envPath);
            }
        }

        if ($savePath === null) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: Missing upload path for file '"
                . Globals::esc($test['test']) . "'. "
                . "Provide it via value or set FG_UPLOAD_PATH in .env",
                400,
                "vE136",
                $devMessage
            );
        }

        $root = self::getProjectRoot();
        $absolutePath = rtrim($root, '/\\') . DIRECTORY_SEPARATOR . ltrim($savePath, '/\\');

        if (!is_dir($absolutePath)) {
            mkdir($absolutePath, 0755, true);
        }

        $destination = self::resolveUniqueFilename(
            $absolutePath,
            basename($data['name'])
        );

        if (!move_uploaded_file($data['tmp_name'], $destination)) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: Failed to save file '"
                . Globals::esc($data['name']) . "'.",
                500,
                "vE137",
                $devMessage
            );
        }

        $data['name'] = basename($destination);
        return $data;
    }


    /**
     * Resolves a unique filename in the given directory, Windows-style.
     * If "file.txt" exists, tries "file (2).txt", "file (3).txt", etc.
     *
     * @param string $directory Absolute path to the target directory.
     * @param string $filename  Original filename (e.g. "photo.jpg").
     * @return string           Full absolute path with a unique filename.
     */
    private static function resolveUniqueFilename(string $directory, string $filename): string
    {
        $destination = $directory . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($destination)) {
            return $destination;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename  = pathinfo($filename, PATHINFO_FILENAME);

        $counter = 2;
        do {
            $newFilename = $extension !== ''
                ? "{$basename} ({$counter}).{$extension}"
                : "{$basename} ({$counter})";

            $destination = $directory . DIRECTORY_SEPARATOR . $newFilename;
            $counter++;
        } while (file_exists($destination));

        return $destination;
    }


    /**
     * Returns the project root directory by locating the vendor folder in the path.
     *
     * @return string Absolute path to the project root directory.
     */
    private static function getProjectRoot(): string
    {
        $vendorPos = strpos(__DIR__, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);

        if ($vendorPos !== false) {
            return substr(__DIR__, 0, $vendorPos);
        }
        return dirname(__DIR__, 4);
    }
}
