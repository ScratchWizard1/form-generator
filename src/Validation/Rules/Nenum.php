<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Nenum extends Validation implements ValidationInterface
{
    /**
     * Private constructor to prevent instantiation.
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
    /**
     * Validate that the provided string is NOT included in the disallowed enum values.
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
    ): bool|string {

        if (trim($data) == '' || $data == Globals::INTERNAL_EMPTY) {
            return Globals::INTERNAL_EMPTY;
        }

        if (!isset($test['value'])) {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: Missing value for '"
                    . Globals::esc($test['test']) . "'",
                400,
                "vE029",
                $devMessage
            );
        }

        if ($data == Globals::ARRAY_REPLACE_VALUE) {
            $data = Globals::getArray();
        }
        if (is_string($data)) {
            $data = [$data];
        }

        $message = "Validation error: The value ( '"
            . Globals::esc(implode("', '", $data))
            . "' ) must NOT be one of the disallowed values: ( '"
            . Globals::esc(implode("', '", $test['value']))
            . "' ).";

        if (!isset($test['modification'])) {
            $data = implode("", $data);
        }

        $result = !in_array($data, $test['value']);

        if (isset($test['modification'])) {
            if (
                $test['modification'] != "array" &&
                $test['modification'] != "any" &&
                $test['modification'] != "only"
            ) {
                return FormErrorHandler::exceptionOrFalse(
                    "Validation error: Unknown modifier '"
                        . Globals::esc($test['modification']) . "' — the test 'isValid"
                        . Globals::esc($test['test']) . "' does not support this modification.",
                    400,
                    "vE030",
                    $devMessage
                );
            }

            $data = Globals::toArray($data);
            if ($test['modification'] == "array") {
                $result = empty(array_intersect($test['value'], $data));
                $message = "Validation error: The provided values ( '"
                    . Globals::esc(implode("', '", $data))
                    . "' ) must NOT include any of the disallowed values: ( '"
                    . Globals::esc(implode("', '", $test['value']))
                    . "' ).";
            }

            if ($test['modification'] == "any") {
                $result = count(array_intersect($test['value'], $data)) === 0;
                $message = "Validation error: Some provided values ( '"
                    . Globals::esc(implode("', '", $data))
                    . "' ) are not allowed — none of them may match: ( '"
                    . Globals::esc(implode("', '", $test['value']))
                    . "' ).";
            }

            if ($test['modification'] == "only") {
                $result = empty(array_intersect($data, $test['value']));
                $message = "Validation error: Some provided values ( '"
                    . Globals::esc(implode("', '", $data))
                    . "' ) are forbidden. None of these values may appear: ( '"
                    . Globals::esc(implode("', '", $test['value']))
                    . "' ).";
            }
        }

        if ($result) {
            return true;
        }

        return FormErrorHandler::exceptionOrFalse(
            $message,
            422,
            "vE031",
            $devMessage
        );
    }
}
