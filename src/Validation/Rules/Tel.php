<?php

declare(strict_types=1);

namespace FormGenerator\Validation\Rules;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Config\Globals;
use FormGenerator\Config\Config;
use FormGenerator\Validation;
use FormGenerator\Validation\ValidationInterface;

class Tel extends Validation implements ValidationInterface
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
     * Check if the given data is a valid phone number.
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

        $value = $test['value'] ?? null;

        if (!isset($value)) {
            $default = Config::getWithFallback(
                ['FG_PHONE_FALLBACK_REGIONS', 'PHONE_FALLBACK_REGIONS'],
                null
            );
            if ($default !== null && is_string($default)) {
                $default = explode(",", $default);
                $default = array_map("trim", $default);
                $default = array_filter(
                    $default,
                    fn($value) => $value !== ''
                );
                $regions = $default;
            } else {
                $regions = PhoneNumberUtil::getInstance()->getSupportedRegions();
            }
        } else {
            $value = array_map(fn($v) => strtoupper((string)$v), $value);
            foreach ($value as $code) {
                if (!self::isValidCountryCode($code)) {
                    return FormErrorHandler::exceptionOrFalse(
                        "Validation error: Unknown country code '"
                            . Globals::esc($code) . "'— the test 'isValid"
                            . Globals::esc($test['test'])
                            . "' supports only valid ISO 3166-1 alpha-2 country codes.",
                        400,
                        "vE114",
                        $devMessage
                    );
                }
            }
            $regions = $value;
        }
        if (!empty($regions)) {
            $regionList = $regions;
        } else {
            $regionList = [null];
        }

        $formatted = self::isValid($data, $regionList);

        if ($formatted !== false) {
            return $formatted;
        } else {
            return FormErrorHandler::exceptionOrFalse(
                "Validation error: The value '"
                    . Globals::esc($data) . "' is not a valid phone number.",
                422,
                "vE115",
                $devMessage
            );
        }
    }

    /**
     * Validate the phone number against the provided regions.
     *
     * @param string $value
     * @param array<string|null> $regions
     * @return string|false
     */
    private static function isValid(string $value, array $regions): string|false
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        foreach ($regions as $region) {
            if ($region === '') {
                $region = null;
            }

            try {
                $number = $phoneUtil->parse($value, $region);

                if ($phoneUtil->isValidNumber($number)) {
                    return $phoneUtil->format($number, PhoneNumberFormat::INTERNATIONAL);
                }
            } catch (NumberParseException $e) {
                continue;
            }
        }

        if (!in_array(null, $regions, true)) {
            try {
                $number = $phoneUtil->parse($value, null);
                if ($phoneUtil->isValidNumber($number)) {
                    return $phoneUtil->format($number, PhoneNumberFormat::INTERNATIONAL);
                }
            } catch (NumberParseException $e) {
                // ignore
            }
        }

        return false;
    }

    /**
     * Check if the provided country code is valid.
     *
     * @param string $code
     * @return bool
     */
    private static function isValidCountryCode(string $code): bool
    {
        $code = strtoupper($code);
        $regions = PhoneNumberUtil::getInstance()->getSupportedRegions();
        return in_array($code, $regions, true);
    }
}
