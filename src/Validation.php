<?php

declare(strict_types=1);

namespace FormGenerator;

use DateTime;
use FormGenerator\ErrorHandling\FormGeneratorException;
use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Validation\ValidationResult;
use FormGenerator\Validation\Format;
use FormGenerator\Config\Globals;
use FormGenerator\Validation\Rules\Alpha;
use FormGenerator\Validation\Rules\AlphaNumber;
use FormGenerator\Validation\Rules\Boolean;
use FormGenerator\Validation\Rules\Color;
use FormGenerator\Validation\Rules\Date;
use FormGenerator\Validation\Rules\DateTime as DateTimeRule;
use FormGenerator\Validation\Rules\Email;
use FormGenerator\Validation\Rules\Enum;
use FormGenerator\Validation\Rules\Nenum;
use FormGenerator\Validation\Rules\Equal;
use FormGenerator\Validation\Rules\Nequal;
use FormGenerator\Validation\Rules\Extension;
use FormGenerator\Validation\Rules\Mime;
use FormGenerator\Validation\Rules\File;
use FormGenerator\Validation\Rules\Ip;
use FormGenerator\Validation\Rules\Json;
use FormGenerator\Validation\Rules\Length;
use FormGenerator\Validation\Rules\Max;
use FormGenerator\Validation\Rules\Min;
use FormGenerator\Validation\Rules\MaxLength;
use FormGenerator\Validation\Rules\MinLength;
use FormGenerator\Validation\Rules\MaxWords;
use FormGenerator\Validation\Rules\MinWords;
use FormGenerator\Validation\Rules\Month;
use FormGenerator\Validation\Rules\Number;
use FormGenerator\Validation\Rules\Password;
use FormGenerator\Validation\Rules\Regex;
use FormGenerator\Validation\Rules\Required;
use FormGenerator\Validation\Rules\Size;
use FormGenerator\Validation\Rules\StringRule;
use FormGenerator\Validation\Rules\Tel;
use FormGenerator\Validation\Rules\Time;
use FormGenerator\Validation\Rules\Url;
use FormGenerator\Validation\Rules\Week;

class Validation
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
     * Set the accepted character set for validation.
     *
     * @param string $acceptCharset
     * @return void
     */
    public static function acceptCharset(string $acceptCharset): void
    {
        if (!empty(trim($acceptCharset))) {
            $acceptCharset = trim(explode(',', $acceptCharset)[0]);
            Globals::setCharset(strtoupper($acceptCharset));
        }
    }

    /**
     * Validate the provided data against the specified rules.
     *
     * @param string|array<mixed> $data
     * @param array<mixed> $rules
     * @param bool $devMessage
     * @param bool $throw
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    public static function validate(
        string|array $data,
        array $rules,
        bool $devMessage = false,
        bool $throw = false
    ): ValidationResult {
        try {
            $data = self::normalizeData($data);

            if (isset($rules['__CONFIG__'])) {
                $rules[Globals::VALIDATION_CONFIG] = $rules['__CONFIG__'];
                unset($rules['__CONFIG__']);
            }

            if (isset($rules[Globals::VALIDATION_CONFIG])) {
                if (is_array($rules[Globals::VALIDATION_CONFIG])) {
                    $rules[Globals::VALIDATION_CONFIG] = self::assocArrayToString(
                        $rules[Globals::VALIDATION_CONFIG]
                    );
                }
            }

            $sanilateData = self::sanitizeData($data, $rules);
            $data = $sanilateData['data'];
            $rules = $sanilateData['rules'];

            $validData = [];

            $validationKeys = self::validationKeys($data, $rules);

            if (in_array(Globals::VALIDATION_CONFIG, $validationKeys)) {
                $result = self::configValidation(
                    Globals::toStr(
                        $rules[Globals::VALIDATION_CONFIG],
                    ),
                    $devMessage,
                    $throw
                );
                if ($result->isValid()) {
                    $validData[Globals::VALIDATION_CONFIG] = $result->getData();
                } else {
                    /**
                     * @var array{message: string, code: int, id:string, ...} $error
                     */
                    $error = $result->getError();
                    if ($throw) {
                        throw  FormErrorHandler::exception(
                            $error['message'],
                            $error['code'],
                            $error['id']
                        );
                    } else {
                        $error[Globals::VALIDATION_ERROR_MARKER] = true;
                        return new ValidationResult($error);
                    }
                }
            }

            $recResult = self::recursiveValidateData(
                $data,
                $rules,
                $validationKeys,
                $devMessage,
                $throw
            );

            if (!$recResult['success']) {
                /**
                 * @var array{message: string, code: int, id:string, ...} $err
                 */
                $err = $recResult['message'];
                if ($throw) {
                    throw FormErrorHandler::exception(
                        $err['message'],
                        $err['code'],
                        $err['id']
                    );
                } else {
                    $err[Globals::VALIDATION_ERROR_MARKER] = true;
                    return new ValidationResult($err);
                }
            }

            $validData = array_merge(
                Globals::toArray($validData),
                Globals::toArray($recResult['validData'])
            );
            $specialMap = $recResult['specialMap'];

            if (empty($specialMap)) {
                return new ValidationResult(self::sanitize($validData));
            }

            $applyResult = self::recursiveApplySpecialTests(
                $validData,
                Globals::toArray($specialMap),
                $devMessage,
                $throw
            );
            if (!$applyResult['success']) {
                /**
                 * @var array{message: string, code: int, id:string, ...} $err
                 */
                $err = $applyResult['message'];
                if ($throw) {
                    throw FormErrorHandler::exception(
                        $err['message'],
                        $err['code'],
                        $err['id']
                    );
                } else {
                    $err[Globals::VALIDATION_ERROR_MARKER] = true;
                    return new ValidationResult($err);
                }
            }

            $validData = Globals::toArray($applyResult['validData']);

            return new ValidationResult(self::sanitize($validData));
        } catch (FormGeneratorException $e) {
            if ($devMessage) {
                FormErrorHandler::showError($e);
            }
            return new ValidationResult($e->toArray());
        }
    }

    /**
     * Recursively validate the data against the provided rules.
     *
     * @param array<mixed> $data
     * @param array<mixed> $rules
     * @param array<mixed> $validationKeys
     * @param bool $devMessage
     * @param bool $throw
     * @return array<mixed>
     */
    private static function recursiveValidateData(
        array $data,
        array $rules,
        array $validationKeys,
        bool $devMessage,
        bool $throw
    ): array {
        $validData = [];
        $specialMap = [];

        foreach ($data as $key => $value) {
            if (!in_array($key, $validationKeys)) {
                $validData[$key] = $value;
                continue;
            }

            if (!isset($rules[$key])) {
                $validData[$key] = $value;
                continue;
            }

            $rowRules = $rules[$key];

            if (is_array($rowRules)) {
                $rowRules = self::assocArrayToString($rowRules);
            }
            $rowRules = Globals::toStr($rowRules);
            $rowTests = self::getTests($rowRules);

            if (is_string($value)) {
                $file = File::check($value, ['test' => 'isValidFile'], false);
            } elseif (is_array($value)) {
                $file = File::check($value, ['test' => 'isValidFile'], false);
            } else {
                $value = Globals::toStr($value);
                $file = File::check($value, ['test' => 'isValidFile'], false);
            }

            if (is_array($value) && !$file) {
                $validData[$key] = [];
                if (
                    ($rowTests[0]['test'] == "Enum" ||
                        $rowTests[0]['test'] == "Nenum") &&
                    isset($rowTests[0]['modification'])
                ) {
                    Globals::setArray($value);
                    $res = self::applyTestsToScalar(
                        Globals::toStr(Globals::ARRAY_REPLACE_VALUE),
                        Globals::toArray($rowTests),
                        $key,
                        [$key],
                        $devMessage,
                        $throw
                    );
                    if (!$res['success']) {
                        return ['success' => false, 'message' => $res['message']];
                    }
                    $validData[$key] = Globals::getArray();

                    if (!empty($res['special'])) {
                        $specialMap[$key] = Globals::toArray($specialMap[$key]);
                    }
                    continue;
                }
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        $subRec = self::recursiveValidateArrayElements(
                            $subValue,
                            $rowTests,
                            [$key, $subKey],
                            $devMessage,
                            $throw
                        );
                        if (!$subRec['success']) {
                            return ['success' => false, 'message' => $subRec['message']];
                        }

                        $validData[$key] = Globals::toArray($validData[$key]);
                        $validData[$key][$subKey] = $subRec['validData'];

                        if (!empty($subRec['specialMap'])) {
                            $specialMap[$key] = Globals::toArray($specialMap[$key]);
                            $specialMap[$key][$subKey] = $subRec['specialMap'];
                        }
                    } else {
                        $res = self::applyTestsToScalar(
                            Globals::toStr($subValue),
                            Globals::toArray($rowTests),
                            $key,
                            [$subKey],
                            $devMessage,
                            $throw
                        );
                        if (!$res['success']) {
                            return ['success' => false, 'message' => $res['message']];
                        }
                        $validData[$key] = Globals::toArray($validData[$key]);
                        $validData[$key][$subKey] = $res['validValue'];

                        if (!empty($res['special'])) {
                            if (isset($specialMap[$key])) {
                                $specialMap[$key] = Globals::toArray($specialMap[$key]);
                                $specialMap[$key][$subKey] = $res['special'];
                            }
                        }
                    }
                }
                continue;
            }

            $res = self::applyTestsToScalar(
                Globals::toStr($value),
                Globals::toArray($rowTests),
                $key,
                [],
                $devMessage,
                $throw
            );

            if (!$res['success']) {
                return ['success' => false, 'message' => $res['message']];
            }


            if (is_array($res['validValue']) && isset($res['validValue']['value'])) {
                $validData[$key] = $res['validValue']['value'];
            } else {
                $validData[$key] = $res['validValue'];
            }

            if (!empty($res['special'])) {
                $specialMap[$key] = $res['special'];
            }
        }

        return ['success' => true, 'validData' => $validData, 'specialMap' => $specialMap];
    }

    /**
     * Applies the row tests to a scalar value.
     *
     * @param mixed $value
     * @param array<mixed> $rowTests
     * @param string $rootKey
     * @param array<mixed> $subPath
     * @param bool $devMessage
     * @param bool $throw
     * @return array<mixed>
     */
    private static function applyTestsToScalar(
        mixed $value,
        array $rowTests,
        string $rootKey,
        array $subPath = [],
        bool $devMessage = false,
        bool $throw = false
    ): array {
        $validValue = null;
        $special = [];

        foreach ($rowTests as $test) {
            /**
             * @var array{test: string, modification?: string, value?: array<string>} $test
             */
            if (in_array($test['test'], ['Format', 'Null', 'NotNull', 'Ignore'])) {
                $special[$test['test']] = $test;
                if ($test['test'] === 'Ignore') {
                    // nothing special to do now
                }
                continue;
            }

            $testname = "isValid" . Globals::toStr($test['test']);
            if (!method_exists(self::class, $testname)) {
                FormErrorHandler::exception(
                    'Non-existent test:"'
                        . Globals::esc($testname) . '"',
                    404,
                    "vE001"
                );
            }

            $result = self::$testname($value, $rootKey, $test, $devMessage);

            if (!($result instanceof ValidationResult)) {
                FormErrorHandler::exception(
                    'Invalid test result for:"'
                        . Globals::esc($testname) . '"',
                    500,
                    "vE002"
                );
            }

            if ($result->isValid()) {
                $validValue = $result->getData();
                if ($validValue !== null) {
                    /**
                     * @var array{value: mixed, ...} $validValue
                     */

                    $value = $validValue['value'];
                }
            } else {
                $error = $result->getError();
                return ['success' => false, 'message' => $error];
            }
        }

        if ($validValue === null && !empty($special)) {
            $validValue = $value;
        } elseif ($validValue === null) {
            $validValue = $value;
        }

        return ['success' => true, 'validValue' => $validValue, 'special' => $special];
    }

    /**
     * Recursively validate array elements applying all tests except special ones (Format, Null, NotNull, Ignore).
     *
     * @param array<mixed> $arrayValue
     * @param array<mixed> $rowTests
     * @param array<mixed> $currentPath
     * @param bool $devMessage
     * @param bool $throw
     * @return array<mixed>
     */
    private static function recursiveValidateArrayElements(
        array $arrayValue,
        array $rowTests,
        array $currentPath,
        bool $devMessage,
        bool $throw
    ): array {
        $validData = [];
        $specialMap = [];
        foreach ($arrayValue as $k => $v) {
            if (is_array($v)) {
                $sub = self::recursiveValidateArrayElements(
                    Globals::toArray($v),
                    $rowTests,
                    array_merge($currentPath, [$k]),
                    $devMessage,
                    $throw
                );
                if (!$sub['success']) {
                    return ['success' => false, 'message' => $sub['message']];
                }
                $validData[$k] = $sub['validData'];
                if (!empty($sub['specialMap'])) {
                    $specialMap[$k] = $sub['specialMap'];
                }
            } else {
                $res = self::applyTestsToScalar(
                    Globals::toStr($v),
                    $rowTests,
                    Globals::toStr($currentPath[0]),
                    [$k],
                    $devMessage,
                    $throw
                );
                if (!$res['success']) {
                    return ['success' => false, 'message' => $res['message']];
                }
                $validData[$k] = $res['validValue'];
                if (!empty($res['special'])) {
                    $specialMap[$k] = $res['special'];
                }
            }
        }

        return ['success' => true, 'validData' => $validData, 'specialMap' => $specialMap];
    }

    /**
     * Recursively apply special tests (Format, Null, NotNull, Ignore) to the validated data.
     *
     * @param array<mixed> $validData
     * @param array<mixed> $specialMap
     * @param bool $devMessage
     * @param bool $throw
     * @return array<mixed>
     */
    private static function recursiveApplySpecialTests(
        array $validData,
        array $specialMap,
        bool $devMessage,
        bool $throw
    ): array {
        try {
            $applyRecursive = function (&$targetData, $map, $path = []) use (&$applyRecursive, $devMessage, $throw) {
                $map = Globals::toArray($map);
                foreach ($map as $k => $v) {
                    if (
                        is_array($v) &&
                        (isset($v['Format']) ||
                            isset($v['Null']) ||
                            isset($v['NotNull']) ||
                            isset($v['Ignore']))
                    ) {
                        $targetData = Globals::toArray($targetData);
                        if (!array_key_exists($k, $targetData)) {
                            $targetData[$k] = null;
                        }

                        if (isset($v['Format'])) {
                            /**
                             * @var array{test: string, modification?: string, value?: array<string>} $f
                             */
                            $f = Globals::toArray($v['Format']);
                            $result = self::isValidFormat(
                                Globals::toStr($targetData[$k]),
                                Globals::toStr($k),
                                $f,
                                $devMessage,
                                $throw
                            );
                            if ($result->isValid()) {
                                $targetResult = $result->getData();
                                if ($targetResult !== null) {
                                    $targetData[$k] = $targetResult['value'];
                                }
                            } else {
                                $error = $result->getError();
                                return ['success' => false, 'message' => $error];
                            }
                        }
                        if (isset($v['Null'])) {
                            if (is_string($targetData[$k])) {
                                if (trim($targetData[$k]) == '' || $targetData[$k] == Globals::INTERNAL_EMPTY) {
                                    $targetData[$k] = null;
                                }
                            }
                        }

                        if (isset($v['NotNull'])) {
                            if (is_string($targetData[$k])) {
                                if (
                                    trim($targetData[$k]) == ''
                                    || $targetData[$k] == Globals::INTERNAL_EMPTY
                                    || $targetData[$k] == null
                                ) {
                                    $targetData[$k] = Globals::NOTNULL_REPLACE_VALUE;
                                }
                            }
                        }

                        continue;
                    }

                    $targetData = Globals::toArray($targetData);
                    if (!isset($targetData[$k])) {
                        $targetData[$k] = [];
                    }
                    if (is_array($v)) {
                        /**
                         * @var array{success: bool, error?: mixed} $res
                         */
                        $res = $applyRecursive(
                            $targetData[$k],
                            $v,
                            array_merge(Globals::toArray($path), [$k])
                        );
                        if (!$res['success']) {
                            return $res;
                        }
                    } else {
                        continue;
                    }
                }

                return ['success' => true];
            };

            $copyData = $validData;
            /**
             * @var array{success: bool, ...} $res
             */
            $res = $applyRecursive($copyData, $specialMap);
            if (!$res['success']) {
                /**
                 * @var array{success: bool, message: mixed} $res
                 */
                return ['success' => false, 'message' => $res['message']];
            }
            return ['success' => true, 'validData' => $copyData];
        } catch (FormGeneratorException $e) {
            $error = $e->toArray();
            $error[Globals::VALIDATION_ERROR_MARKER] = true;
            return ['success' => false, 'message' => $error];
        } catch (\Exception $e) {
            FormErrorHandler::exception(
                $e->getMessage(),
                500,
                "vE004"
            );
        }
    }

    /**
     * Validate if the given data contains only alphabetic characters.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidAlpha(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Alpha::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' may contain letters only.",
                "Validation error: The '"
                    . Globals::esc($data) . "' value does not match the Alpha rule 
                    – only alphabetic characters (A–Z, a–z) are allowed.",
                422,
                "vE005",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data contains only alphanumeric characters.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidAlphaNumber(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = AlphaNumber::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' may contain letters and numbers only.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The entered value '"
                    . Globals::esc($data) . "' does not match the AlphaNumber rule 
                    – only alphabetic characters (A–Z, a–z) and digits (0–9) are allowed.",
                422,
                "vE008",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid boolean value.
     *
     * @param string|bool $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidBoolean(
        string|bool $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Boolean::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' must be a boolean value only.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The entered value '"
                    . Globals::esc($data) . "' does not match the Boolean rule 
                    – only true or false are allowed.",
                422,
                "vE011",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid hex color code.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidColor(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Color::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' must be a valid color code only.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The entered value '"
                    . Globals::esc($data) . "' does not match the color format 
                    – it must be a hex code like #RRGGBB.",
                422,
                "vE013",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid date.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidDate(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Date::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' must be a valid date only.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The entered value '"
                    . Globals::esc($data) . "' does not match the date format 
                    – it must be YYYY-MM-DD.",
                422,
                "vE015",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid datetime.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidDateTime(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = DateTimeRule::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' must be a valid datetime only.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The entered value '"
                    . Globals::esc($data) . "' does not match the datetime format 
                    – it must be YYYY-MM-DD\THH:MM.",
                422,
                "vE017",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid email.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidEmail(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Email::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' must be a valid datetime only.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The entered value '"
                    . Globals::esc($data) . "'does not match the datetime format 
                    – it must be YYYY-MM-DD\THH:MM.",
                422,
                "vE019",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is in the allowed enum values.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidEnum(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Enum::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' must be one of the allowed enum values.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The entered value '"
                    . Globals::esc($data) . "' is not in the list of allowed enum values.",
                422,
                "vE024",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is not in the disallowed enum values.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidNenum(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Nenum::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' is not allowed.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is in the list of disallowed values.",
                422,
                "vE028",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is equal to the specified value.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidEqual(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Equal::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' must be exactly one allowed value.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is not the allowed value.",
                422,
                "vE032",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is not equal to the specified value.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidNequal(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Nequal::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' is not allowed.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is disallowed.",
                422,
                "vE035",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data has a valid extension.
     *
     * @param string|array{name: string, type: string, tmp_name: string, error: int, size: int} $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidExtension(
        string|array $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Extension::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' is not allowed.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is disallowed.",
                422,
                "vE038",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data has a valid MIME type.
     *
     * @param string|array{name: string, type: string, tmp_name: string, error: int, size: int} $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidMime(
        string|array $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Mime::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' is not allowed.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is disallowed.",
                422,
                "vE043",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data has a valid size.
     *
     * @param string|array{name: string, type: string, tmp_name: string, error: int, size: int} $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidSize(
        array|string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Size::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key)
                    . "': The uploaded file exceeds the maximum allowed size.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The uploaded file '"
                    . Globals::esc($data) . "' exceeds the maximum allowed size.",
                422,
                "vE048",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid file.
     *
     * @param string|array{name: string, type: string, tmp_name: string, error: int, size: int} $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidFile(
        string|array $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = File::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' must be a valid file.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is not a valid file.",
                422,
                "vE054",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid IP address.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidIp(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Ip::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "The entered value for key '"
                    . Globals::esc($key) . "' must be a valid IP address.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is not a valid IP address.",
                422,
                "vE058",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid JSON string.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidJson(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Json::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The provided value is not valid JSON.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is not a valid JSON string.",
                422,
                "vE062",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data has a valid length.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidLength(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Length::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The provided value is not valid JSON.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is not a valid JSON string.",
                422,
                "vE066",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data does not exceed the maximum value.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidMax(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Max::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The value exceeds the allowed limit.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data)  . "' exceeds the allowed limit.",
                422,
                "vE070",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data meets the minimum value.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidMin(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Min::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The value is below the allowed minimum.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is below the allowed minimum.",
                422,
                "vE075",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data does not exceed the maximum length.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidMaxLength(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = MaxLength::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The value has length "
                    . strlen($data) . " and exceeds the allowed maximum.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data)  . "' has length "
                    . strlen($data) . " and exceeds the allowed maximum.",
                422,
                "vE080",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data meets the minimum length.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidMinLength(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = MinLength::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The value has length "
                    . strlen($data) . " and is below the allowed minimum.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' has length "
                    . strlen($data) . " and is below the allowed minimum.",
                422,
                "vE084",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data does not exceed the maximum word count.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidMaxWords(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = MaxWords::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The value has "
                    . preg_match_all('/\p{L}+/u', $data, $matches)
                    . " words and exceeds the allowed maximum.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' has "
                    . preg_match_all('/\p{L}+/u', $data, $matches)
                    . " words and exceeds the allowed maximum.",
                422,
                "vE088",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data meets the minimum word count.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidMinWords(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = MinWords::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The value has "
                    . preg_match_all('/\p{L}+/u', $data, $matches)
                    . " words and is below the allowed minimum.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' has "
                    . preg_match_all('/\p{L}+/u', $data, $matches)
                    . " words and is below the allowed minimum.",
                422,
                "vE091",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid month.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidMonth(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Month::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The value is not a valid month.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is not a valid month (expected format: YYYY-MM).",
                422,
                "vE094",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid number.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidNumber(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Number::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The value is not a valid number.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data)  . "' is not a valid number.",
                422,
                "vE096",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid password.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidPassword(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Password::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key)
                    . "': The password is not valid."
                    . " (expect 1 uppercase, 1 lowercase, 1 number, min 8 chars)",
                "Validation error for key '"
                    . Globals::esc($key)
                    . "': The password does not meet the required format."
                    . " (expect 1 uppercase, 1 lowercase, 1 number, min 8 chars)",
                422,
                "vE104",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data matches the specified regex pattern.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidRegex(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Regex::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key)
                    . "': The value does not match the required pattern (regex: '"
                    . Globals::esc(Globals::getRegex()) . "').",
                "Validation error for key '"
                    . Globals::esc($key)
                    . "': The value '" . Globals::esc($data)
                    . "' does not match the required pattern (regex: '"
                    . Globals::esc(Globals::getRegex())  . "').",
                422,
                "vE107",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is present (required).
     *
     * @param array<string>|string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidRequired(
        array|string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Required::check($data, $test, $devMessage);
        if ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key)
                    . "': The value is required and cannot be empty.",
                "Validation error for key '"
                    . Globals::esc($key)
                    . "': The value is required and cannot be empty.",
                422,
                "vE112",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid string.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidStringRule(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = StringRule::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } else {
            return new ValidationResult(['value' => $data]);
        }
    }

    /**
     * Validate if the given data is a valid telephone number.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidTel(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Tel::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key)
                    . "': The provided value is not valid phone number.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is not a valid phone number.",
                422,
                "vE113",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid time.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidTime(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Time::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The provided value is not valid time.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is not a valid time.",
                422,
                "vE116",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid URL.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidUrl(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Url::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key)
                    . "': The provided value is not url address.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is not a url address.",
                422,
                "vE118",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data is a valid week.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $test
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidWeek(
        string $data,
        string $key,
        array $test,
        bool $devMessage = false
    ): ValidationResult {
        $result = Week::check($data, $test, $devMessage);
        if (is_string($result)) {
            return new ValidationResult(['value' => $result]);
        } elseif ($result === true) {
            return new ValidationResult(['value' => $data]);
        } else {
            return new ValidationResult(FormErrorHandler::fatalError(
                "Validation error for key '"
                    . Globals::esc($key) . "': The provided value is not week.",
                "Validation error for key '"
                    . Globals::esc($key) . "': The value '"
                    . Globals::esc($data) . "' is not a week.",
                422,
                "vE122",
                $devMessage
            ));
        }
    }

    /**
     * Validate if the given data matches the specified format.
     *
     * @param string $data
     * @param string $key
     * @param array{test: string, modification?: string, value?: array<string>} $format
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return ValidationResult
     */
    protected static function isValidFormat(
        string $data,
        string $key,
        array $format,
        bool $devMessage = false,
        bool $throw = false
    ): ValidationResult {
        try {
            $result = Format::format($data, $format, $key, $devMessage);
            return new ValidationResult(['value' => $result]);
        } catch (FormGeneratorException $e) {
            if ($devMessage && !$throw) {
                FormErrorHandler::showError($e);
            }
            $error = $e->toArray();
            $error[Globals::VALIDATION_ERROR_MARKER] = true;
            return new ValidationResult($error);
        }
    }

    /**
     * Validate and normalize the configuration string.
     *
     * @param string $row
     * @return ValidationResult
     */
    private static function configValidation(
        string $row,
        bool $devMessage = true,
        bool $throw = false
    ): ValidationResult {
        $delimiter = strpos($row, '|') !== false ? '|' : ';';
        $row = array_filter(
            array_map(
                'trim',
                explode($delimiter, $row)
            ),
            fn($v) => $v !== ''
        );
        $result = self::normalizeConfig(
            $row,
            $devMessage,
            $throw
        );
        if (!$result->isValid()) {
            return $result;
        } else {
            $data = $result->getData() ?? [];
            return new ValidationResult($data);
        }
    }

    /**
     * Normalize the configuration settings.
     *
     * @param array<string> $data
     * @return ValidationResult
     */
    private static function normalizeConfig(
        array $data,
        bool $devMessage = true,
        bool $throw = true
    ): ValidationResult {
        $html = "true";
        $special = "true";
        $fhtml = "false";
        $fspecial = "false";
        try {
            foreach ($data as $rules) {
                $rule = explode(":", $rules);
                if (!isset($rule[1])) {
                    return new ValidationResult(FormErrorHandler::fatalError(
                        "Invalid configuration in '__CONFIG__': missing value for setting name:'"
                            . Globals::esc($rule[0]) . "'. Expected format: &lt;SettingName&gt;:true/false",
                        "Invalid configuration in '__CONFIG__': missing value for setting name:'"
                            . Globals::esc($rule[0]) . "'. Expected format: &lt;SettingName&gt;:true/false",
                        422,
                        "vE132",
                        $devMessage
                    ));
                }
                switch (strtolower(trim($rule[0]))) {
                    case 'h':
                    case 'html':
                    case 'htmlspecial':
                    case 'htmlspecialchars':
                        if (in_array(strtolower($rule[1]), ['f', 'false'])) {
                            $html = "false";
                        }
                        break;
                    case 's':
                    case 'st':
                    case 'strip':
                    case 'striptags':
                    case 'strip_tags':
                        if (in_array(strtolower($rule[1]), ['f', 'false'])) {
                            $special = "false";
                        }
                        break;
                    case 'fh':
                    case 'fhtml':
                    case 'fhtmlspecial':
                    case 'fhtmlspecialchars':
                        if (in_array(strtolower($rule[1]), ['t', 'true'])) {
                            $fhtml = "true";
                        }
                        break;
                    case 'fs':
                    case 'fst':
                    case 'fstrip':
                    case 'fstriptags':
                    case 'fstrip_tags':
                        if (in_array(strtolower($rule[1]), ['t', 'true'])) {
                            $fspecial = "true";
                        }
                        break;
                    default:
                        return new ValidationResult(FormErrorHandler::fatalError(
                            "Invalid setting name '"
                                . Globals::esc($rule[0])
                                . "' in '__CONFIG__': this setting does not exist",
                            "Invalid setting name '"
                                . Globals::esc($rule[0])
                                . "' in '__CONFIG__': this setting does not exist",
                            422,
                            "vE133",
                            $devMessage
                        ));
                }
            }
            return new ValidationResult([
                'html' => $html,
                'strip' => $special,
                'fhtml' => $fhtml,
                'fstrip' => $fspecial,
            ]);
        } catch (FormGeneratorException $e) {
            if ($devMessage && !$throw) {
                FormErrorHandler::showError($e);
            }
            $error = $e->toArray();
            $error[Globals::VALIDATION_ERROR_MARKER] = true;
            return new ValidationResult($error);
        }
    }

    /**
     * Sanitize the input data based on configuration settings.
     *
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public static function sanitize(array $data): array
    {

        if (isset($data[Globals::VALIDATION_CONFIG]) && is_array($data[Globals::VALIDATION_CONFIG])) {
            $html = isset($data[Globals::VALIDATION_CONFIG]['html']) ?
                filter_var($data[Globals::VALIDATION_CONFIG]['html'], FILTER_VALIDATE_BOOLEAN)  : true;
            $strip = isset($data[Globals::VALIDATION_CONFIG]['strip']) ?
                filter_var($data[Globals::VALIDATION_CONFIG]['strip'], FILTER_VALIDATE_BOOLEAN)  : true;
        } else {
            $html = true;
            $strip = true;
        }

        if (isset($data[Globals::VALIDATION_CONFIG])) {
            unset($data[Globals::VALIDATION_CONFIG]);
        }
        foreach ($data as $key => $value) {
            if (
                $value == null ||
                $value == Globals::NOTNULL_REPLACE_VALUE
            ) {
                continue;
            }
            if ($value instanceof DateTime) {
                $data[$key] = $value;
                continue;
            }
            if (is_string($value)) {
                $file = File::check($value, ['test' => 'isValidFile'], false);
            } elseif (is_array($value)) {
                $file = File::check($value, ['test' => 'isValidFile'], false);
            } else {
                $value = Globals::toStr($value);
                $file = File::check($value, ['test' => 'isValidFile'], false);
            }

            if (is_array($value) && !$file) {
                $data[$key] = self::sanitize($value);
            }

            if (is_string($value)) {
                if ($strip) {
                    $value = strip_tags(trim($value));
                }
                if ($html) {
                    $value = Globals::esc($value);
                }
                $data[$key] = $value;
            }
        }

        foreach ($data as $key => $value) {
            if (
                $value == null
            ) {
                continue;
            }

            if ($value instanceof DateTime) {
                $data[$key] = $value;
                continue;
            }

            if (is_string($value)) {
                $file = File::check($value, ['test' => 'isValidFile'], false);
            } elseif (is_array($value)) {
                $file = File::check($value, ['test' => 'isValidFile'], false);
            } else {
                $value = Globals::toStr($value);
                $file = File::check($value, ['test' => 'isValidFile'], false);
            }

            if (is_array($value) && !$file) {
                $data[$key] = self::sanitize($value);
            }

            if (is_string($value)) {
                if (trim($value) == '' || $value == Globals::INTERNAL_EMPTY || $value == "[empty]") {
                    unset($data[$key]);
                }
            }
            if ($value == Globals::NOTNULL_REPLACE_VALUE) {
                $data[$key] = '';
            }
        }
        return $data;
    }

    /**
     * Sanitize data and adjust rules based on required and null tests.
     *
     * @param array<mixed> $data
     * @param array<mixed> $rules
     * @return array{data: array<mixed>, rules: array<mixed>}
     */
    private static function sanitizeData(array $data, array $rules): array
    {
        foreach ($rules as $key => $rule) {
            if (is_array($rule)) {
                $rule = self::assocArrayToString($rule);
            }
            $rule = Globals::toStr($rule);
            $rowTests = self::getTests($rule);
            $finalRowTest = [];
            foreach ($rowTests as $rowTest) {
                $finalRowTest[] = $rowTest['test'];
            }
            if (!isset($data[$key])) {
                if (in_array('Required', $finalRowTest)) {
                    $data[$key] = Globals::INTERNAL_EMPTY;
                }
                if (in_array('Null', $finalRowTest)) {
                    $data[$key] = Globals::INTERNAL_EMPTY;
                }
                if (in_array('NotNull', $finalRowTest)) {
                    $data[$key] = Globals::INTERNAL_EMPTY;
                }
            }
        }

        return [
            'data' => $data,
            'rules' => $rules
        ];
    }

    /**
     * Get the keys that need validation based on data and rules.
     *
     * @param array<mixed> $data
     * @param array<mixed> $rules
     * @return array<mixed>
     */
    private static function validationKeys(array $data, array $rules): array
    {
        $result = array_intersect(array_keys($data), array_keys($rules));

        if (array_key_exists(Globals::VALIDATION_CONFIG, $rules)) {
            $result[] = Globals::VALIDATION_CONFIG;
        }
        return $result;
    }

    /**
     * Convert an associative array to a string representation.
     *
     * @param array<mixed> $data
     * @param string $parentKey
     * @return string
     */
    protected static function assocArrayToString(array $data, string $parentKey = ''): string
    {
        $parts = [];
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $currentKey = is_array($value) ? '' : Globals::toStr($value);
            } else {
                $currentKey = $parentKey !== '' ? $parentKey . ':' . Globals::toStr($key) : Globals::toStr($key);
            }
            if (is_array($value)) {
                if (is_numeric(array_key_first($value))) {
                    $parts[] = $currentKey . ':' . implode('\x1F', $value);
                } else {
                    $parts[] = self::assocArrayToString($value, $currentKey);
                }
                continue;
            }

            if (is_numeric($key)) {
                $parts[] = $value;
                continue;
            }

            if ($value === true) {
                $parts[] = $key;
                continue;
            }

            if (is_string($value)) {
                $parts[] = $currentKey . ':' . $value;
            }
        }

        return implode("\x7C", $parts);
    }

    /**
     * Parse the test definitions from a configuration string.
     *
     * @param string $row
     * @return array<array{test: string, modification?: string, value?: array<mixed>}>
     */
    private static function getTests(string $row): array
    {
        if (preg_match('/\b(ig|ignor|ignore)\b/i', $row, $matches)) {
            return [['test' => 'Ignore']];
        }

        $result = [];
        if (preg_match('/(re|reg|regex):\s*(.+)/i', $row, $matches)) {
            $parts = preg_split('/[;|](?![^\/]*\/)/', $matches[2]);
            if ($parts === false) {
                $regex = '[empty]';
            } else {
                $regex = $parts[0];
            }
            Globals::setRegex($regex);
            $row = str_replace($regex, Globals::REGEX_REPLACE_VALUE, $row);
        }

        if (strpos($row, "\x7C") !== false) {
            $delimiter = "\x7C";
        } else {
            $delimiter = strpos($row, ';') !== false ? ';' : '|';
        }
        $row = explode($delimiter, $row);
        foreach ($row as $test) {
            if (trim($test) != '') {
                $result[] = self::getTestModificationValue($test);
            }
        }
        return $result;
    }

    /**
     * Extract test, modification, and value from a modification string.
     *
     * @param string $data
     * @return array{test: string, modification?: string, value?: array<string>}
     */
    public static function getTestModificationValue(string $data): array
    {
        $testsWitchValue = [
            'Enum',
            'Nenum',
            'Equal',
            'Nequal',
            'Extension',
            'Length',
            'Mime',
            'Min',
            'Max',
            'MinLength',
            'MaxLength',
            'MinWords',
            'MaxWords',
            'Regex',
            'Size',
            'Tel'
        ];
        $result = [];
        $data = self::splitModification($data);

        $result['test'] = self::normalizeTestName($data[0]);
        if (isset($data[1])) {
            if (isset($data[2])) {
                $result['modification'] = self::normalizeModificationName($data[1]);
                $result['value'] = self::normalizeValue($data[2]);
            } else {
                if (in_array($result['test'], $testsWitchValue)) {
                    $result['value'] = self::normalizeValue($data[1]);
                } else {
                    $result['modification'] = self::normalizeModificationName($data[1]);
                }
            }
        }
        return $result;
    }

    /**
     * Split modification string into parts considering special cases.
     *
     * @param string $modification
     * @return array<string>
     */
    private static function splitModification(string $modification): array
    {
        $parts = explode(":", $modification, 2);
        if (isset($parts[1])) {
            if (in_array(substr($parts[1], 0, 2), ["v6"])) {
                $subparts = explode(":", $parts[1], 2);
                $parts = array_merge([$parts[0]], $subparts);
            } else {
                preg_match_all('/(?=(\d:\d))/', $parts[1], $matches);
                $y = substr_count($parts[1], ':');
                $x = count($matches[0]);
                if ($y != $x) {
                    $subparts = explode(":", $parts[1], 2);
                    $parts = array_merge([$parts[0]], $subparts);
                }
            }
        }
        return $parts;
    }

    /**
     * Normalize the input data to ensure consistent types.
     *
     * @param string|array<mixed> $data
     * @return array<mixed>
     */
    public static function normalizeData(string|array $data): array
    {
        $result = self::normalizeDataRecursive($data);


        return $result;
    }

    /**
     * Recursively normalize data values.
     *
     * @param string|array<mixed> $data
     * @return array<mixed>
     */
    private static function normalizeDataRecursive(string|array $data): array
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return is_array($decoded) ? $decoded : [$decoded];
            }
            return [$data];
        }

        $normalized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $normalized[$key] = self::normalizeDataRecursive($value);
            } elseif (is_bool($value)) {
                $normalized[$key] = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $normalized[$key] = 'null';
            } elseif (is_numeric($value)) {
                $normalized[$key] = (string)$value;
            } else {
                if (
                    is_scalar($value) ||
                    (is_object($value) &&
                        method_exists($value, '__toString'))
                ) {
                    $normalized[$key] = (string)$value;
                } else {
                    continue;
                }
            }
        }

        return $normalized;
    }

    /**
     * Normalize the given value.
     *
     * @param string $test
     * @return string
     */
    private static function normalizeTestName(string $test): string
    {
        switch (strtolower(trim($test))) {
            case 'a':
            case 'al':
            case 'alpha':
                return 'Alpha';
            case 'an':
            case 'aln':
            case 'alphan':
            case 'alnum':
            case 'alphanum':
            case 'alphanumber':
                return 'AlphaNumber';
            case 'b':
            case 'bool':
            case 'boolean':
                return 'Boolean';
            case 'c':
            case 'color':
                return 'Color';
            case 'd':
            case 'date':
                return 'Date';
            case 'dt':
            case 'datetime':
                return 'DateTime';
            case 'e':
            case 'mail':
            case 'email':
                return 'Email';
            case 'in':
            case 'en':
            case 'enum':
                return 'Enum';
            case 'nin':
            case 'nen':
            case 'nenum':
                return 'Nenum';
            case 'eq':
            case 'equal':
                return 'Equal';
            case 'neq':
            case 'nequal':
                return 'Nequal';
            case 'ex':
            case 'exten':
            case 'extension':
                return 'Extension';
            case 'mim':
            case 'mime':
                return 'Mime';
            case 'f':
            case 'file':
                return 'File';
            case 'fo':
            case 'for':
            case 'form':
            case 'format':
                return 'Format';
            case 'i':
            case 'ip':
                return 'Ip';
            case 'j':
            case 'json':
                return 'Json';
            case 'l':
            case 'len':
            case 'length':
                return 'Length';
            case 'ma':
            case 'max':
                return 'Max';
            case 'mal':
            case 'malen':
            case 'malength':
            case 'maxl':
            case 'maxlen':
            case 'maxlength':
                return 'MaxLength';
            case 'maw':
            case 'maword':
            case 'mawords':
            case 'maxw':
            case 'maxword':
            case 'maxwords':
                return 'MaxWords';
            case 'mi':
            case 'min':
                return 'Min';
            case 'mil':
            case 'milen':
            case 'milength':
            case 'minl':
            case 'minlen':
            case 'minlength':
                return 'MinLength';
            case 'miw':
            case 'miword':
            case 'miwords':
            case 'minw':
            case 'minword':
            case 'minwords':
                return 'MinWords';
            case 'm':
            case 'month':
                return 'Month';
            case 'n':
            case 'num':
            case 'number':
                return 'Number';
            case 'null':
                return 'Null';
            case 'nnull':
            case 'notnull':
                return 'NotNull';
            case 'p':
            case 'pas':
            case 'pass':
            case 'password':
                return 'Password';
            case 're':
            case 'reg':
            case 'regex':
                return 'Regex';
            case 'r':
            case 'req':
            case 'required':
                return 'Required';
            case 'si':
            case 'size':
                return 'Size';
            case 's':
            case 'str':
            case 'string':
                return 'StringRule';
            case 'te':
            case 'tel':
                return 'Tel';
            case 'ti':
            case 'time':
                return 'Time';
            case 'u':
            case 'url':
                return 'Url';
            case 'w':
            case 'week':
                return 'Week';
            default:
                $test = trim($test);
                if ($test == "") {
                    return "null";
                }
                return strtoupper($test[0]) . strtolower(substr($test, 1));
        }
    }

    /**
     * Normalize the given modification name.
     *
     * @param string $modification
     * @return string
     */
    private static function normalizeModificationName(string $modification): string
    {
        $result = [];
        $modifications = explode(",", $modification);
        $modifications = array_filter(
            $modifications,
            fn($value) => $value !== ''
        );
        if (empty($modifications)) {
            return "null";
        }
        foreach ($modifications as $modification) {
            switch (strtolower(trim($modification))) {
                case 'a':
                case 'ar':
                case 'array':
                    $result[] = 'array';
                    break;
                case 'an':
                case 'any':
                    $result[] = 'any';
                    break;
                case 'c':
                case 'cut':
                    $result[] = 'cut';
                    break;
                case 'b':
                case 'bool':
                case 'boolean':
                    $result[] = 'boolean';
                    break;
                case 'd':
                case 'domain':
                    $result[] = 'domain';
                    break;
                case 'dt':
                case 'datetime':
                    $result[] = 'datetime';
                    break;
                case 'e':
                case 'exist':
                    $result[] = 'exist';
                    break;
                case 'r':
                case 'rou':
                case 'round':
                    $result[] = 'round';
                    break;
                case 'f':
                case 'float':
                    $result[] = 'float';
                    break;
                case 'i':
                case 'int':
                case 'integer':
                    $result[] = 'integer';
                    break;
                case 'h':
                case 'hash':
                    $result[] = 'hash';
                    break;
                case 'o':
                case 'only':
                    $result[] = 'only';
                    break;
                case 'st':
                case 'strict':
                    $result[] = 'strict';
                    break;
                case 's':
                case 'str':
                case 'string':
                    $result[] = 'string';
                    break;
                case 'v6':
                    $result[] = 'v6';
                    break;
                default:
                    if (strtolower(trim($modification)) == "") {
                        $result[] = "null";
                        break;
                    }
                    $result[] = strtolower(trim($modification));
                    break;
            }
        }
        return implode("\x1F", $result);
    }

    /**
     * Normalize the given value for validation.
     *
     * @param string $value
     * @return array<string>
     */
    private static function normalizeValue(string $value): array
    {
        $value = explode('\x1F', $value);
        if (!isset($value[1])) {
            if (strpos($value[0], ',') !== false) {
                $value = explode(',', $value[0]);
            }
        }
        $value = array_map("trim", $value);
        $value = array_filter($value, fn($v) => $v !== '');
        return $value;
    }
}
