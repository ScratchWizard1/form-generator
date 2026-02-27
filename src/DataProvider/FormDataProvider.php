<?php

declare(strict_types=1);

namespace FormGenerator\DataProvider;

use FormGenerator\Config\Config;
use FormGenerator\Config\Globals;
use FormGenerator\Validation;
use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\ErrorHandling\FormGeneratorException;
use FormGenerator\Validation\ValidationResult;
use Google\Cloud\RecaptchaEnterprise\V1\Client\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\CreateAssessmentRequest;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;
use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use RuntimeException;
use UnexpectedValueException;
use TypeError;
use Error;

class FormDataProvider
{
    /**
     * Retrieve and validate form data based on the provided parameters.
     *
     * @param string $formID
     * @param string $projectId
     * @param string $sitekey
     * @param array<mixed> $validation
     * @param string|null $userProvidedCredentials
     * @param bool $devMessage
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return \FormGenerator\Validation\ValidationResult
     */
    public static function getDataFrom(
        string $formID,
        string $projectId,
        string $sitekey,
        array $validation = [],
        ?string $userProvidedCredentials = null,
        bool $devMessage = false
    ): ValidationResult {
        try {
            if (!in_array($_SERVER['REQUEST_METHOD'] ?? '', ['POST', 'GET'], true)) {
                return new ValidationResult(FormErrorHandler::fatalError(
                    "Error sending. Please try again.",
                    'Invalid request method. Good methods are: POST, GET. 
                    Request method is: ' . Globals::esc(($_SERVER['REQUEST_METHOD'] ?? 'unknown')),
                    405,
                    'pE001',
                    $devMessage
                ));
            }

            /**
             * Execute this block only for safe, expected HTTP methods.
             *
             * This ensures that the code runs exclusively for POST or GET requests.
             * Other HTTP methods (e.g., PUT, DELETE, PATCH) are intentionally ignored.
             *
             *  PHPStan notes:
             * - The POST and GET checks are flagged as always true by PHPStan due to
             *   strict type analysis, but at runtime $_SERVER['REQUEST_METHOD'] can be
             *   any HTTP method.
             *
             * - Inline phpstan-ignore-line is only used on lines where PHPStan actually
             *   raises warnings.
             */
            if (
                (($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['formID']) || isset($_POST[$formID])))
                    || ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['formID']) || isset($_GET[$formID]))))
            ) {
                if (empty(trim($formID))) {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        if (!isset($_POST['formID']) || empty($_POST['formID'])) {
                            $formID = Globals::INTERNAL_EMPTY;
                        } else {
                            $formID = $_POST['formID'];
                        }
                    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') { // @phpstan-ignore-line
                        if (!isset($_GET['formID']) || empty($_GET['formID'])) {
                            $formID = Globals::INTERNAL_EMPTY;
                        } else {
                            $formID = $_GET['formID'];
                        }
                    } else {
                        return new ValidationResult(FormErrorHandler::fatalError(
                            "Error sending. Plesae try again.",
                            'Invalid request method. Good methods are: POST, GET.'
                                . 'Request method is: ' . Globals::esc($_SERVER['REQUEST_METHOD']),
                            405,
                            'pE002',
                            $devMessage
                        ));
                    }
                    $formID = Globals::toStr($formID);
                } else {
                    $formID = Globals::toStr($formID);
                }

                if ($formID == Globals::INTERNAL_EMPTY) {
                    return new ValidationResult([]);
                }

                if (empty(trim($formID))) {
                    return new ValidationResult(FormErrorHandler::fatalError(
                        "Error sending. Plesae try again.",
                        'Empty formID value.',
                        400,
                        'pE003',
                        $devMessage
                    ));
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (!isset($_POST[$formID])) {
                        return new ValidationResult(FormErrorHandler::fatalError(
                            "Error sending. Plesae try again.",
                            'undefined formID value.',
                            400,
                            'pE004',
                            $devMessage
                        ));
                    }
                    $data = $_POST[$formID];
                } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') { // @phpstan-ignore-line
                    if (!isset($_GET[$formID])) {
                        return new ValidationResult(FormErrorHandler::fatalError(
                            "Error sending. Plesae try again.",
                            'undefined formID value.',
                            400,
                            'pE004',
                            $devMessage
                        ));
                    }
                    $data = $_GET[$formID];
                } else {
                    return new ValidationResult(FormErrorHandler::fatalError(
                        "Error sending. Plesae try again.",
                        'Invalid request method. Good methods are: POST, GET.'
                            . 'Request method is: ' . Globals::esc($_SERVER['REQUEST_METHOD']),
                        405,
                        'pE005',
                        $devMessage
                    ));
                }

                /**
                 * @var array{
                 *     csrf?: string,
                 *     honeypot?: string,
                 *     g-recaptcha-response?: string,
                 *     captcha-action?: string,
                 *     ...
                 * } $data
                 */

                if (empty($data)) {
                    return new ValidationResult(FormErrorHandler::fatalError(
                        "Error sending. Plesae try again.",
                        'Missing or invalid value in hidden ipnuts and inputs',
                        404,
                        'pE006',
                        $devMessage
                    ));
                }

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                if (!isset($_SESSION[$formID]) || !is_array($_SESSION[$formID])) {
                    session_regenerate_id(true);
                    return new ValidationResult(FormErrorHandler::fatalError(
                        "Error sending. Please try again.",
                        'Invalid CSRF session token.',
                        403,
                        'pE007',
                        $devMessage
                    ));
                }

                if (
                    !isset($_SESSION[$formID]['csrf'])
                    || !isset($data['csrf'])
                    || empty(trim(Globals::toStr($_SESSION[$formID]['csrf'])))
                    || empty(trim(Globals::toStr($data['csrf'])))
                    || !hash_equals(
                        Globals::toStr($_SESSION[$formID]['csrf']),
                        Globals::toStr($data['csrf'])
                    )
                ) {
                    session_regenerate_id(true);
                    return new ValidationResult(FormErrorHandler::fatalError(
                        "Error sending. Please try again.",
                        'Invalid CSRF token.',
                        403,
                        'pE008',
                        $devMessage
                    ));
                }
                if (isset($data['g-recaptcha-response'])) {
                    if (empty($data['g-recaptcha-response'])) {
                        return new ValidationResult(FormErrorHandler::fatalError(
                            "Error sending. Please try again.",
                            'Missing reCAPTCHA token.',
                            422,
                            'pE009',
                            $devMessage
                        ));
                    }
                    if (empty($data['captcha-action'])) {
                        return new ValidationResult(FormErrorHandler::fatalError(
                            "Error sending. Please try again.",
                            'Missing reCAPTCHA action. You must define the 
                            action manually by adding ->captcha("yourAction") 
                            when building your form.',
                            422,
                            'pE010',
                            $devMessage
                        ));
                    }
                    if (empty(trim($sitekey))) {
                        return new ValidationResult(FormErrorHandler::exception(
                            'Missing reCAPTCHA siteKey.',
                            422,
                            'pE011'
                        ));
                    }
                    if (empty(trim($projectId))) {
                        return new ValidationResult(FormErrorHandler::exception(
                            'Missing reCAPTCHA project.',
                            422,
                            'pE012'
                        ));
                    }

                    if (
                        !self::testCaptcha(
                            $projectId,
                            $sitekey,
                            Globals::toStr($data['g-recaptcha-response']),
                            $userProvidedCredentials,
                            Globals::toStr($data['captcha-action']),
                            $devMessage
                        )
                    ) {
                        return new ValidationResult(FormErrorHandler::fatalError(
                            "Error sending. Please try again.",
                            'Invalid reCAPTCHA response.',
                            422,
                            'pE013',
                            $devMessage
                        ));
                    }
                }

                if (!empty($data['honeypot'])) {
                    return new ValidationResult(FormErrorHandler::fatalError(
                        "Error sending. Please try again.",
                        'Bot detected via honeypot field.',
                        422,
                        'pE014',
                        $devMessage
                    ));
                }

                $data = array_diff_key($data, array_flip([
                    'csrf',
                    'PHPSESSID',
                    'honeypot',
                    'g-recaptcha-response',
                    'captcha-action'
                ]));

                if (
                    isset($_FILES[$formID])
                    && is_array($_FILES[$formID])
                    && isset($_FILES[$formID]['name'])
                    && is_array($_FILES[$formID]['name'])
                ) {
                    /** @var array{
                     *     name: array<int,string>,
                     *     type: array<int,string>,
                     *     tmp_name: array<int,string>,
                     *     error: array<int,int>,
                     *     size: array<int,int>
                     * } $FILESRow
                     */
                    $FILESRow = $_FILES[$formID];
                    $files = [];
                    foreach ($FILESRow['name'] as $key => $name) {
                        $files[$key] = [
                            'name' => $name,
                            'type' => $FILESRow['type'][$key],
                            'tmp_name' => $FILESRow['tmp_name'][$key],
                            'error' => $FILESRow['error'][$key],
                            'size' => $FILESRow['size'][$key],
                        ];
                    }
                    foreach ($files as $key => $file) {
                        $data[$key] = $file;
                    }
                }

                if ($validation == []) {
                    return new ValidationResult(Validation::sanitize($data));
                }
                $result = Validation::validate(
                    $data,
                    $validation,
                    $devMessage,
                    true
                );
                if (!$result->isValid()) {
                    $error = $result->getError();

                    if (empty($error)) {
                        return new ValidationResult([]);
                    }

                    /**
                     * @var array{message: string, code: int, id:string} $error
                     */
                    return new ValidationResult([FormErrorHandler::fatalError(
                        $error['message'],
                        $error['message'],
                        $error['code'],
                        $error['id'],
                        $devMessage
                    )]);
                }

                return $result;
            } else {
                return new ValidationResult([]);
            }
        } catch (FormGeneratorException $e) {
            if ($devMessage) {
                FormErrorHandler::showError($e);
            }
            return new ValidationResult($e->toArray());
        }
    }

    /**
     * Test Google reCAPTCHA v3 token validity using Google Cloud RecaptchaEnterpriseServiceClient.
     *
     * @param string $projectId Google Cloud project ID.
     * @param string $siteKey reCAPTCHA site key.
     * @param string $token reCAPTCHA token to validate.
     * @param string|null $userProvidedCredentials Optional path to Google credentials JSON file.
     * @param string $expectedAction Expected action name for the reCAPTCHA token.
     * @param bool $devMessage Whether to show detailed error messages for developers.
     * @throws \FormGenerator\ErrorHandling\FormGeneratorException
     * @return bool True if the token is valid and passes all checks, false otherwise.
     */
    private static function testCaptcha(
        string $projectId,
        string $siteKey,
        string $token,
        ?string $userProvidedCredentials = null,
        string $expectedAction = 'GENERAL',
        bool $devMessage = false
    ): bool {
        if (empty($token)) {
            return FormErrorHandler::exceptionOrFalse(
                'ReCAPTCHA token is missing.',
                422,
                "pE015",
                $devMessage
            );
        }

        try {
            $credentials = Config::getWithFallback(
                ['FG_GOOGLE_APPLICATION_CREDENTIALS', 'GOOGLE_APPLICATION_CREDENTIALS']
            );
            $credentials = Globals::toStr($credentials);
            if ($credentials) {
                $credentials = self::joinPaths(dirname(__DIR__, 4), $credentials);
            }

            if (!$credentials && $userProvidedCredentials) {
                $credentials = self::joinPaths(dirname(__DIR__, 4), $userProvidedCredentials);
            }

            if (!$credentials) {
                $defaultPath = self::joinPaths(dirname(__DIR__, 4), 'config/google-recaptcha.json');
                if (file_exists($defaultPath)) {
                    $credentials = $defaultPath;
                }
            }

            if (!$credentials || !file_exists($credentials)) {
                return FormErrorHandler::exceptionOrFalse(
                    'reCAPTCHA credentials JSON not found.
                    Please set the FG_GOOGLE_APPLICATION_CREDENTIALS environment variable,
                    provide a path to the JSON file, or place the file at:
                    root\config\google-recaptcha.json',
                    500,
                    "pE016",
                    $devMessage
                );
            }
            $client = new RecaptchaEnterpriseServiceClient([
                'credentials' => $credentials
            ]);
            $projectName = $client->projectName($projectId);

            $event = (new Event())
                ->setSiteKey($siteKey)
                ->setToken($token)
                ->setUserIpAddress(Globals::toStr($_SERVER['REMOTE_ADDR']));

            $assessment = (new Assessment())
                ->setEvent($event);

            $request = (new CreateAssessmentRequest())
                ->setParent($projectName)
                ->setAssessment($assessment);

            $response = $client->createAssessment($request);

            $tokenProps = $response->getTokenProperties();

            if ($tokenProps === null) {
                return FormErrorHandler::exceptionOrFalse(
                    'Token properties missing from reCAPTCHA response.',
                    500,
                    "pE017",
                    $devMessage
                );
            }

            if (!$tokenProps->getValid()) {
                $reason = InvalidReason::name($tokenProps->getInvalidReason());
                return FormErrorHandler::exceptionOrFalse(
                    'Invalid reCAPTCHA token: ' . Globals::esc($reason),
                    422,
                    "pE018",
                    $devMessage
                );
            }

            $action = $tokenProps->getAction();
            if (strcasecmp(trim($action), trim($expectedAction)) !== 0) {
                return FormErrorHandler::exceptionOrFalse(
                    'reCAPTCHA action mismatch. Expected "'
                        . Globals::esc($expectedAction) . '", got "'
                        . Globals::esc($action) . '".',
                    422,
                    "pE019",
                    $devMessage
                );
            }

            $riskAnalysis = $response->getRiskAnalysis();
            if ($riskAnalysis === null) {
                return FormErrorHandler::exceptionOrFalse(
                    'RiskAnalysis missing from reCAPTCHA response.',
                    500,
                    "pE020",
                    $devMessage
                );
            }

            $score = $riskAnalysis->getScore();
            if ($score < 0.5) {
                return FormErrorHandler::exceptionOrFalse(
                    'reCAPTCHA score too low ('
                        . Globals::esc($score)
                        . '). Possible bot detected.',
                    422,
                    "pE021",
                    $devMessage
                );
            }

            return true;
        } catch (FormGeneratorException $e) {
            return false;
        } catch (ApiException $e) {
            return FormErrorHandler::exceptionOrFalse(
                'Google API error: ' . $e->getMessage(),
                500,
                "pE022",
                $devMessage
            );
        } catch (ValidationException $e) {
            return FormErrorHandler::exceptionOrFalse(
                'Validation error: ' . $e->getMessage(),
                422,
                "pE023",
                $devMessage
            );
        } catch (RuntimeException | UnexpectedValueException | TypeError | Error $e) {
            return FormErrorHandler::exceptionOrFalse(
                'Unexpected error: ' . $e->getMessage(),
                500,
                "pE024",
                $devMessage
            );
        }
    }

    /**
     * Joins multiple path segments into a single normalized path.
     *
     * @param string ...$paths Path segments to join.
     * @return string The joined and normalized path.
     */
    private static function joinPaths(string ...$paths): string
    {
        $result = preg_replace(
            '#/+#',
            '/',
            join(
                '/',
                array_map(fn($p) => trim($p, '/\\'), $paths)
            )
        );
        return $result ?? '';
    }
}
