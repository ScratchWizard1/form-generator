<?php

declare(strict_types=1);

namespace FormGenerator\ErrorHandling;

use FormGenerator\Config\Config;
use FormGenerator\Config\Globals;
use FormGenerator\ErrorHandling\FormGeneratorException;

class FormErrorHandler
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
     * Handle a fatal error by either throwing an exception or returning an error array.
     *
     * @param string $userMessage
     * @param string $devMessage
     * @param int $code
     * @param string $id
     * @param bool $debugMessage
     * @return array{message:string, code:int, id:string, file:string, line:int, ... }
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     */
    public static function fatalError(
        string $userMessage,
        string $devMessage,
        int $code,
        string $id,
        bool $debugMessage = false,
    ): array {
        $backtrace = self::getBacktrace();
        if ($debugMessage) {
            throw new FormGeneratorException(
                $devMessage,
                $code,
                $id,
                $backtrace['file'],
                $backtrace['line']
            );
        }
        return [
            'message' => $userMessage,
            'code'  => $code,
            'id'    => $id,
            'file' => $backtrace['file'],
            'line' => $backtrace['line'],
            Globals::VALIDATION_ERROR_MARKER => true
        ];
    }

    /**
     * Either throw an exception or return false based on the debug flag.
     *
     * @param string $devMessage
     * @param int $code
     * @param string $id
     * @param bool $debugMessage
     * @return bool
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     */
    public static function exceptionOrFalse(
        string $devMessage,
        int $code,
        string $id,
        bool $debugMessage = false,
    ): bool {
        $backtrace = self::getBacktrace();
        if ($debugMessage) {
            throw new FormGeneratorException(
                $devMessage,
                $code,
                $id,
                $backtrace['file'],
                $backtrace['line']
            );
        } else {
            return false;
        }
    }

    /**
     * Always throw an exception with the provided details.
     *
     * @param string $devMessage
     * @param int $code
     * @param string $id
     * @return never
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     */
    public static function exception(
        string $devMessage,
        int $code,
        string $id,
    ): never {
        $backtrace = self::getBacktrace();
        throw new FormGeneratorException(
            $devMessage,
            $code,
            $id,
            $backtrace['file'],
            $backtrace['line']
        );
    }

    /**
     * Display an error message if developer errors are enabled.
     *
     * @param FormGeneratorException $e
     * @return void
     */
    public static function showError(FormGeneratorException $e): void
    {
        $showDevErrors = filter_var(
            Config::getWithFallback(
                ['FG_DEFAULT_SHOW_DEV_ERRORS', 'DEFAULT_SHOW_DEV_ERRORS'],
                true
            ),
            FILTER_VALIDATE_BOOLEAN
        );
        if ($showDevErrors) {
            $error = "<div class='error'"
                . "style='color:red;font-weight:bold;"
                . "padding:10px;"
                . "border-radius:5px;"
                . "background-color:#ffe6e6;"
                . "border: solid 1px red;"
                . "'>Message: "
                . $e->getMessage()
                . " | ID: "
                . $e->getId()
                . " | Code: "
                . $e->getCode()
                . " | File: "
                . $e->getFileOrigin()
                . " | Line: "
                . $e->getLineOrigin()
                . "</div>";
            echo $error;
        }
    }

    /**
     * Get the backtrace information based on the provided coordinator.
     *
     * @return array{file: string, line: int}
     */
    private static function getBacktrace(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $filtered = array_values(array_filter($trace, function ($frame) {
            return isset($frame['file']) && strpos($frame['file'], 'vendor') === false;
        }));

        $frame = $filtered[0] ?? null;

        return [
            'file' => $frame['file'] ?? 'unknown',
            'line' => $frame['line'] ?? 0,
        ];
    }
}
