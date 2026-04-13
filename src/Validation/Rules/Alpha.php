<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Alpha extends Validation implements ValidationInterface
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
     * Check if the given data is a valid alpha string.
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
        if (isset($test['modification'])) {
            if ($test['modification'] != "only") {
                return FormErrorHandler::exceptionOrFalse(
                    "Validation error: Unknown modifier '"
                        . Globals::esc($test['modification']) . "' — the test 'isValid"
                        . Globals::esc($test['test']) . "' does not support this modification. ",
                    400,
                    "vE006",
                    $devMessage
                );
            }
            $result = [];
            $chars = mb_str_split($data);
            foreach ($chars as $char) {
                if (preg_match('/^[\p{L}\s]$/u', $char)) {
                    $result[] = $char;
                }
            }
            return trim(implode("", $result));
        }
        if (preg_match('/^[\p{L}\s]+$/u', $data)) {
            return true;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The string '"
                    . Globals::esc($data) . "' contains non-letter characters.",
                422,
                "vE007",
                $devMessage
            );
        }
    }
}
