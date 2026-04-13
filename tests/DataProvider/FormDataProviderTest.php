<?php

declare(strict_types=1);

namespace FormGenerator\Tests\DataProvider;

use PHPUnit\Framework\TestCase;
use FormGenerator\DataProvider\FormDataProvider;
use FormGenerator\ErrorHandling\FormGeneratorException;
use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\Validation\ValidationResult;
use FormGenerator\Validation\Format;
use FormGenerator\Config\Globals;
use FormGenerator\Config\Config;
use FormGenerator\Validation\Rules\Alpha;
use FormGenerator\Validation\Rules\AlphaNumber;
use FormGenerator\Validation\Rules\Boolean;
use FormGenerator\Validation\Rules\Color;
use FormGenerator\Validation\Rules\Date;
use FormGenerator\Validation\Rules\DateTime;
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

#[\PHPUnit\Framework\Attributes\CoversClass(FormDataProvider::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(FormGeneratorException::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(FormErrorHandler::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(ValidationResult::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Format::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Config::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Alpha::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(AlphaNumber::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Boolean::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Color::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Date::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DateTime::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Email::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Enum::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Nenum::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Equal::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Nequal::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Extension::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Mime::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(File::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Ip::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Json::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Length::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Max::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Min::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(MaxLength::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(MinLength::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(MaxWords::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(MinWords::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Month::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Number::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Password::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Regex::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Required::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Size::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(StringRule::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Tel::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Time::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Url::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Week::class)]
final class FormDataProviderTest extends TestCase
{
    private FormDataProvider $formDataProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $_SERVER = [];
        $_POST = [];
        $_GET = [];
        $_FILES = [];
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $this->formDataProvider = new FormDataProvider();
    }

    public function testDataProviderIsInstanceOfFormDataProvider(): void
    {
        $this->assertInstanceOf(
            FormDataProvider::class,
            $this->formDataProvider
        );
    }



    #[\PHPUnit\Framework\Attributes\DataProvider('dataFromProvider')]
    public function testDataFromCases(
        string $formID,
        array $session,
        array $server,
        array $post,
        array $validation,
        bool $expectedValid,
        string $sitekey,
        string $projectId,
        string $credentials,
        bool $error
    ): void {
        if ($error) {
            $_SERVER = $server;
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = $session;
            if ($server['REQUEST_METHOD'] === "POST") {
                $_POST = $post;
            } else {
                $_GET = $post;
            }
            $pattern = "/<div class='error'"
                . "style='color:red;font-weight:bold;"
                . "padding:10px;"
                . "border-radius:5px;"
                . "background-color:#ffe6e6;"
                . "border: solid 1px red;'"
                . ">(.*?)<\/div>/s";

            $result = FormDataProvider::getDataFrom(
                $formID,
                $projectId,
                $sitekey,
                $validation,
                $credentials,
                true
            );
            $this->expectOutputRegex(
                $pattern
            );
        } else {
            $_SERVER = $server;
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = $session;
            if ($server['REQUEST_METHOD'] === "POST") {
                $_POST = $post;
            } else {
                $_GET = $post;
            }
            $result = FormDataProvider::getDataFrom(
                $formID,
                $projectId,
                $sitekey,
                $validation,
                $credentials,
                false
            );
            $this->assertInstanceOf(ValidationResult::class, $result);
            $this->assertSame($expectedValid, $result->isValid());
        }
    }

    public static function dataFromProvider(): array
    {
        return [
            'valid POST' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                true,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'invalid CSRF (POST)' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'invalid_token',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'invalid Honeypot (POST)' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => 'invalit_honeypot',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'missing form ID in Post (POST)' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => '',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                true,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'missing form ID in getDataFrom (POST)' => [
                '',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                true,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'missing form ID in Post and getDataFrom (POST)' => [
                '',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => '',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'valid POST with validation 1' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [
                    "Name" => "string; req;",
                    "Email" => "email; req"
                ],
                true,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'valid POST with validation 2 with format' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'Vek' => '18',
                        'honeypot' => '',
                    ]
                ],
                [
                    "Name" => "string; req;",
                    "Email" => "email; req",
                    "Vek" => "number; min:18; f:r"
                ],
                true,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'valid GET' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                true,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'invalid CSRF (GET)' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'invalid_token',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'invalid Honeypot (GET)' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => 'invalit_honeypot',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'missing form ID in Post (GET)' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => '',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                true,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'missing form ID in getDataFrom (GET)' => [
                '',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                true,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'missing form ID in Post and getDataFrom (GET)' => [
                '',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => '',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'valid GET with validation 1' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [
                    "Name" => "string; req;",
                    "Email" => "email; req"
                ],
                true,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'valid GET with validation 2 with format' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'Vek' => '18',
                        'honeypot' => '',
                    ]
                ],
                [
                    "Name" => "string; req;",
                    "Email" => "email; req",
                    "Vek" => "number; min:18; f:r"
                ],
                true,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'Invalid request method' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'INVALID', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'Interanl empty formID' => [
                Globals::INTERNAL_EMPTY,
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => Globals::INTERNAL_EMPTY,
                    Globals::INTERNAL_EMPTY => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                false
            ],
            'Error pE001 - invalid value' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'INVALID', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                true
            ],
            'Error pE004 - invalid value (POST)' => [
                '',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'invalid',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                true
            ],
            'Error pE004 - invalid value (GET)' => [
                '',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'GET', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'invalid',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                true
            ],
            'Error pE006 - empty data' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => []
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                true
            ],
            'Error pE007 - invalid value' => [
                'ContactForm',
                ['csrf' => 'token123'],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                true
            ],
            'Error pE008 - invalid value' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'invalid-value',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                true
            ],
            'Error pE009 - empty value' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                        'g-recaptcha-response' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                true
            ],
            'Error pE010 - empty value' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                        'g-recaptcha-response' => 'response',
                        'captcha-action' => '',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                true
            ],
            'Error pE011 - empty value' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                        'g-recaptcha-response' => 'response',
                        'captcha-action' => 'action',
                    ]
                ],
                [],
                false,
                '',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                true
            ],
            'Error pE012 - empty value' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                        'g-recaptcha-response' => 'response',
                        'captcha-action' => 'action',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                '',
                '/path/to/credentials.json',
                true
            ],
            'Error pE022 - permission denied' => [
                'ContactForm',
                ['ContactForm' => ['csrf' => 'token123']],
                ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'],
                [
                    'formID' => 'ContactForm',
                    'ContactForm' => [
                        'csrf' => 'token123',
                        'Name' => 'Janko',
                        'Email' => 'janko@example.com',
                        'honeypot' => '',
                        'g-recaptcha-response' => 'response',
                        'captcha-action' => 'action',
                    ]
                ],
                [],
                false,
                'DUMMY_SITEKEY',
                'DUMMY_PROJECT_ID',
                '/path/to/credentials.json',
                true
            ],
        ];
    }
}
