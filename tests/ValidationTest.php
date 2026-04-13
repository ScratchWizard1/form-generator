<?php

declare(strict_types=1);

namespace FormGenerator\Tests;

use FormGenerator\Config\Config;
use FormGenerator\Config\Globals;
use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\ErrorHandling\FormGeneratorException;
use FormGenerator\Validation;
use FormGenerator\Validation\Format;
use FormGenerator\Validation\Rules\Alpha;
use FormGenerator\Validation\Rules\AlphaNumber;
use FormGenerator\Validation\Rules\Boolean;
use FormGenerator\Validation\Rules\Color;
use FormGenerator\Validation\Rules\Date;
use FormGenerator\Validation\Rules\DateTime as DateTimeRule;
use FormGenerator\Validation\Rules\Email;
use FormGenerator\Validation\Rules\Enum;
use FormGenerator\Validation\Rules\Equal;
use FormGenerator\Validation\Rules\Extension;
use FormGenerator\Validation\Rules\File;
use FormGenerator\Validation\Rules\Ip;
use FormGenerator\Validation\Rules\Json;
use FormGenerator\Validation\Rules\Length;
use FormGenerator\Validation\Rules\Max;
use FormGenerator\Validation\Rules\MaxLength;
use FormGenerator\Validation\Rules\MaxWords;
use FormGenerator\Validation\Rules\Mime;
use FormGenerator\Validation\Rules\Min;
use FormGenerator\Validation\Rules\MinLength;
use FormGenerator\Validation\Rules\MinWords;
use FormGenerator\Validation\Rules\Month;
use FormGenerator\Validation\Rules\Nenum;
use FormGenerator\Validation\Rules\Nequal;
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
use FormGenerator\Validation\ValidationResult;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(Validation::class)]
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
#[\PHPUnit\Framework\Attributes\CoversClass(DateTimeRule::class)]
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
final class ValidationTest extends TestCase
{
    public function testValidationCannotBeInstantiated()
    {
        $this->expectException(\Error::class);
        new \FormGenerator\Validation();
    }

    public function testValidationIsStaticUtility()
    {
        $reflection = new \ReflectionClass(\FormGenerator\Validation::class);

        $this->assertTrue($reflection->isInstantiable() === false);
    }

    public function testSetAcceptCharset()
    {
        Validation::acceptCharset("ISO-8859-2");
        $this->assertSame("ISO-8859-2", Globals::getCharset());
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('validationRulesProvider')]
    public function testValidation(
        string|array $data,
        array $rules,
        string|bool $expected = true,
    ) {
        if (is_string($expected)) {
            putenv('FG_DEFAULT_GET_DEV_ERRORS=true');
            putenv('FG_DEFAULT_SHOW_DEV_ERRORS=true');

            if ($expected == "showDevError") {
                $pattern = "/<div class='error'"
                    . "style='color:red;font-weight:bold;"
                    . "padding:10px;"
                    . "border-radius:5px;"
                    . "background-color:#ffe6e6;"
                    . "border: solid 1px red;'"
                    . ">(.*?)<\/div>/s";

                $result = Validation::validate($data, $rules, true, false);
                $this->expectOutputRegex(
                    $pattern
                );
            }
            if ($expected == "getError") {
                $result = Validation::validate($data, $rules, false, false);
                $result = $result->getError();

                $this->assertIsArray($result);

                $this->assertArrayHasKey('message', $result);
                $this->assertArrayHasKey('code', $result);
                $this->assertArrayHasKey('id', $result);
                $this->assertArrayHasKey('file', $result);
                $this->assertArrayHasKey('line', $result);

                $this->assertIsString($result['message']);
                $this->assertIsInt($result['code']);
                $this->assertIsString($result['id']);
                $this->assertIsString($result['file']);
                $this->assertIsInt($result['line']);
            }
        } else {
            $result = Validation::validate($data, $rules, false);
            $this->assertEquals($expected, $result->isValid());
            $this->assertInstanceOf(ValidationResult::class, $result);
        }
    }
    public static function validationRulesProvider(): array
    {
        return [
            'All valid' => [
                [
                    'alpha' => 'TestString',
                    'alphaNumber' => 'Test123',
                    'boolean' => true,
                    'boolean2' => '1',
                    'color' => '#ff5733',
                    'date' => '2023-10-05',
                    'dateTime' => '2023-10-05 14:30:00',
                    'email' => 'john.doe@example.com',
                    'enum' => 'option1',
                    'nenum' => 'not_in_enum',
                    'equal' => 'match',
                    'nequal' => 'match',
                    'ip' => '192.168.1.1',
                    'json' => '{"key": "value"}',
                    'length' => '12345',
                    'max-number' => 50.5,
                    'max-timestamp' => '22503680000',
                    'max-datetime_tz' => '2026-01-01T00:00:00+00:00',
                    'max-datetime_sec' => '2026-01-01T00:00:00',
                    'max-datetime' => '2026-01-01T00:00',
                    'max-date' => '2026-01-01',
                    'max-time_sec' => '00:00:00',
                    'max-time' => '00:00',
                    'max-week' => '2026-W01',
                    'max-year_month' => '2026-01',
                    'max-year' => '2026',
                    'min-number' => 50.5,
                    'min-timestamp' => '22503680000',
                    'min-datetime_tz' => '2025-01-01T00:00:00+00:00',
                    'min-datetime_sec' => '2025-01-01T00:00:00',
                    'min-datetime' => '2025-01-01T00:00',
                    'min-date' => '2025-01-01',
                    'min-time_sec' => '23:58:59',
                    'min-time' => '23:58',
                    'min-week' => '2025-W01',
                    'min-year_month' => '2025-01',
                    'min-year' => '2025',
                    'maxLength' => 'short',
                    'minLength' => 'longerstring',
                    'maxWords' => 'one two three',
                    'minWords' => 'one two three four five',
                    'month' => '2023-10',
                    'number' => 42,
                    'password' => 'P@ssw0rd!',
                    'regex' => 'Abc123',
                    'required' => 'I am required',
                    'string' => 'Just a string',
                    'tel' => '+421 912 345 678',
                    'time' => '14:30:00',
                    'url' => 'https://www.example.com',
                    'week' => '2023-W40',
                    'ignore' => 'This field is ignored in validation',
                    'null' => '',
                    'notnull' => ''
                ],
                [
                    'alpha' => 'alpha',
                    'alphaNumber' => 'alphaNumber',
                    'boolean' => 'boolean',
                    'boolean2' => 'boolean',
                    'color' => 'color',
                    'date' => 'date',
                    'dateTime' => 'dateTime',
                    'email' => 'email',
                    'enum' => 'enum:option1,option2,option3',
                    'nenum' => 'nenum:option1,option2,option3',
                    'equal' => 'equal:match',
                    'nequal' => 'nequal:notmatch',
                    'extension' => 'extension:txt',
                    'ip' => 'ip',
                    'json' => 'json',
                    'length' => 'length:5',
                    'max-number' => 'max:100',
                    'max-timestamp' => 'max:32503680000',
                    'max-datetime_tz' => 'max:2026-01-01T12:30:45+00:00',
                    'max-datetime_sec' => 'max:2026-01-01T12:30:45',
                    'max-datetime' => 'max:2026-01-01T12:30',
                    'max-date' => 'max:2026-12-31',
                    'max-time_sec' => 'max:23:59:59',
                    'max-time' => 'max:23:59',
                    'max-week' => 'max:2026-W52',
                    'max-year_month' => 'max:2026-12',
                    'max-year' => 'max:2030',
                    'min-number' => 'min:40',
                    'min-timestamp' => 'min:22500000000',
                    'min-datetime_tz' => 'min:2024-12-31T12:30:45+00:00',
                    'min-datetime_sec' => 'min:2024-12-31T12:30:45',
                    'min-datetime' => 'min:2024-12-31T12:30',
                    'min-date' => 'min:2024-12-30',
                    'min-time_sec' => 'min:21:58:59',
                    'min-time' => 'min:21:58',
                    'min-week' => 'min:2024-W52',
                    'min-year_month' => 'min:2024-12',
                    'min-year' => 'min:2024',
                    'maxLength' => 'maxLength:10',
                    'minLength' => 'minLength:5',
                    'maxWords' => 'maxWords:5',
                    'minWords' => 'minWords:3',
                    'month' => 'month',
                    'number' => 'number',
                    'password' => 'password',
                    'regex' => 'regex:/^[a-z0-9]+$/i',
                    'required' => 'required',
                    'string' => 'string',
                    'tel' => 'tel',
                    'time' => 'time',
                    'url' => 'url',
                    'week' => 'week',
                    'ignore' => 'ignore',
                    'null' => 'null',
                    'notnull' => 'notnull'
                ]
            ],
            'All empty valid' => [
                [
                    'alpha' => '',
                    'alphaNumber' => '',
                    'boolean' => '',
                    'color' => '',
                    'date' => '',
                    'dateTime' => '',
                    'email' => '',
                    'enum' => '',
                    'nenum' => '',
                    'equal' => '',
                    'nequal' => '',
                    'ip' => '',
                    'json' => '',
                    'length' => '',
                    'max' => '',
                    'min' => '',
                    'maxLength' => '',
                    'minLength' => '',
                    'maxWords' => '',
                    'minWords' => '',
                    'month' => '',
                    'number' => '',
                    'password' => '',
                    'regex' => '',
                    'string' => '',
                    'tel' => '',
                    'time' => '',
                    'url' => '',
                    'week' => '',
                    'ignore' => '',
                ],
                [
                    'alpha' => 'alpha',
                    'alphaNumber' => 'alphaNumber',
                    'boolean' => 'boolean',
                    'color' => 'color',
                    'date' => 'date',
                    'dateTime' => 'dateTime',
                    'email' => 'email',
                    'enum' => 'enum:option1,option2,option3',
                    'nenum' => 'nenum:option1,option2,option3',
                    'equal' => 'equal:match',
                    'nequal' => 'nequal:notmatch',
                    'extension' => 'extension:txt',
                    'ip' => 'ip',
                    'json' => 'json',
                    'length' => 'length:5',
                    'max' => 'max:100',
                    'min' => 'min:5',
                    'maxLength' => 'maxLength:10',
                    'minLength' => 'minLength:5',
                    'maxWords' => 'maxWords:5',
                    'minWords' => 'minWords:3',
                    'month' => 'month',
                    'number' => 'number',
                    'password' => 'password',
                    'regex' => 'regex:/^[a-z0-9]+$/i',
                    'string' => 'string',
                    'tel' => 'tel',
                    'time' => 'time',
                    'url' => 'url',
                    'week' => 'week',
                    'ignore' => 'ignore',
                ]
            ],
            'All valid witch modifikations' => [
                [
                    'alpha' => 'TestString',
                    'alphaNumber' => 'Test123',
                    'email' => 'example@gmail.com',
                    'enum' => ['option1', 'option2'],
                    'enum2' => ['option1', 'option2', 'option3'],
                    'enum3' => ['option1', 'option2', 'option3'],
                    'nenum'  => 'nenum:o:option3',
                    'nenum2' => 'nenum:a:option4',
                    'nenum3' => 'nenum:an:option4',
                    'ip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
                    'json' => '{"key": "value"}',
                    'length' => '123456',
                    'length2' => '12345',
                    'length3' => '1234',
                    'maxLength' => 'longstring',
                    'minLength' => 'short',
                    'number' => 42,
                    'number2' => 42.5,
                    'number3' => 42.5,
                    'number4' => 42.58,
                    'number5' => 'sd-fgh1dfgh3e2.t5',
                    'number6' => 42,
                    'password' => 'P@ssw0rd!',
                    'url' => 'https://google.com',
                ],
                [
                    'alpha' => 'alpha:o',
                    'alphaNumber' => 'alphaNumber:o',
                    'email' => 'email:d',
                    'enum' => 'enum:o:option1,option2,option3',
                    'enum2' => 'enum:a:option1,option2',
                    'enum3' => 'enum:an:option1',
                    'nenum'  => 'nenum:o:option3',
                    'nenum2' => 'nenum:a:option4',
                    'nenum3' => 'nenum:an:option4',
                    'ip' => 'ip:v6',
                    'json' => 'json:ar',
                    'length' => 'length:c:5',
                    'length2' => 'length:c:5',
                    'length3' => 'length:c:5',
                    'maxLength' => 'maxLength:c:5',
                    'minLength' => 'minLength:c:10',
                    'number' => 'number:i',
                    'number2' => 'number:f',
                    'number3' => 'number:o,i',
                    'number4' => 'number:o,f',
                    'number5' => 'number:o',
                    'number6' => 'number:o,f',
                    'password' => 'password:h',
                    'url' => 'url:e',
                ]
            ],
            "Non-existent test error vE001" => [
                ['test' => 'test'],
                ['test' => 'test'],
                'showDevError'
            ],
            "Aplha error vE005 - invalid value" => [
                ['alpha' => 'alpha-E-007'],
                ['alpha' => 'alpha'],
                'getError'
            ],
            "Aplha error vE006 - invalid modification" => [
                ['alpha' => 'alpha'],
                ['alpha' => 'alpha:e'],
                'showDevError'
            ],
            "Aplha error vE007 - invalid value" => [
                ['alpha' => 'alpha-E-007'],
                ['alpha' => 'alpha'],
                'showDevError'
            ],
            "AplhaNumber error vE008 - invalid value" => [
                ['alphanumber' => 'alphanumber-E-007'],
                ['alphanumber' => 'alphanumber'],
                'getError'
            ],
            "AplhaNumber error vE009 - invalid value" => [
                ['alphanumber' => 'alphanumber-E-007'],
                ['alphanumber' => 'alphanumber'],
                'showDevError'
            ],
            "AplhaNumber error vE010 - invalid modification" => [
                ['alphanumber' => 'alphanumber'],
                ['alphanumber' => 'alphanumber:e'],
                'showDevError'
            ],
            "Boolean error vE011 - invalid value" => [
                ['boolean' => 'invalid-value'],
                ['boolean' => 'boolean'],
                'getError'
            ],
            "Boolean error vE012 - invalid value" => [
                ['boolean' => 'invalid-value'],
                ['boolean' => 'boolean'],
                'showDevError'
            ],
            "Color error vE013 - invalid value" => [
                ['color' => 'invalid-value'],
                ['color' => 'color'],
                'getError'
            ],
            "Color error vE014 - invalid value" => [
                ['color' => 'invalid-value'],
                ['color' => 'color'],
                'showDevError'
            ],
            "Date error vE015 - invalid value" => [
                ['date' => 'invalid-value'],
                ['date' => 'date'],
                'getError'
            ],
            "Date error vE016 - invalid value" => [
                ['date' => 'invalid-value'],
                ['date' => 'date'],
                'showDevError'
            ],
            "DateTime error vE017 - invalid value" => [
                ['datetime' => 'invalid-value'],
                ['datetime' => 'datetime'],
                'getError'
            ],
            "DateTime error vE018 - invalid value" => [
                ['datetime' => 'invalid-value'],
                ['datetime' => 'datetime'],
                'showDevError'
            ],
            "Email error vE019 - invalid value" => [
                ['email' => 'example[at sign]gmail.com'],
                ['email' => 'email'],
                'getError'
            ],
            "Email error vE020 - invalid modification" => [
                ['email' => 'example@gmail.com'],
                ['email' => 'email:h'],
                'showDevError'
            ],
            "Email error vE021 - invalid value" => [
                ['email' => 'example[at sign]gmail.com'],
                ['email' => 'email:d'],
                'showDevError'
            ],
            "Email error vE022 - invalid value" => [
                ['email' => 'example@invalid.com'],
                ['email' => 'email:d'],
                'showDevError'
            ],
            "Email error vE023 - invalid value" => [
                ['email' => 'invalid-value'],
                ['email' => 'email'],
                'showDevError'
            ],
            "Enum error vE024 - invalid value" => [
                ['enum' => 'invalid-value'],
                ['enum' => 'enum:enum'],
                'getError'
            ],
            "Enum error vE025 - empty test value" => [
                ['enum' => 'enum'],
                ['enum' => 'enum'],
                'showDevError'
            ],
            "Enum error vE026 - invalid modification" => [
                ['enum' => 'enum'],
                ['enum' => 'enum:e:enum'],
                'showDevError'
            ],
            "Enum error vE027 - invalid value" => [
                ['enum' => 'invalid-value'],
                ['enum' => 'enum:enum'],
                'showDevError'
            ],
            "Equal error vE032 - invalid value" => [
                ['equal' => 'invalid-value'],
                ['equal' => 'equal:equal'],
                'getError'
            ],
            "Equal error vE033 - empty test value" => [
                ['equal' => 'equal'],
                ['equal' => 'equal'],
                'showDevError'
            ],
            "Equal error vE034 - invalid value" => [
                ['equal' => 'invalid-value'],
                ['equal' => 'equal:equal'],
                'showDevError'
            ],
            "Ip error vE058 - invalid value" => [
                ['ip' => 'invalid-value'],
                ['ip' => 'ip'],
                'getError'
            ],
            "Ip error vE059 - invalid modification" => [
                ['ip' => '193.87.11.26'],
                ['ip' => 'ip:e'],
                'showDevError'
            ],
            "Ip error vE060 - invalid value" => [
                ['ip' => '193.87.11.26'],
                ['ip' => 'ip:v6'],
                'showDevError'
            ],
            "Ip error vE061 - invalid value" => [
                ['ip' => 'invalid-value'],
                ['ip' => 'ip'],
                'showDevError'
            ],
            "Json error vE062 - invalid value" => [
                ['json' => '{"key": "val}'],
                ['json' => 'json'],
                'getError'
            ],
            "Json error vE063 - invalid modification" => [
                ['json' => '{"key": "value"}'],
                ['json' => 'json:e'],
                'showDevError'
            ],
            "Json error vE064 - invalid value" => [
                ['json' => '{"key": "val}'],
                ['json' => 'json'],
                'showDevError'
            ],
            "Json error vE065 - invalid value" => [
                ['json' => 'false'],
                ['json' => 'json:a'],
                'showDevError'
            ],
            "Length error vE066 - invalid value" => [
                ['length' => 'length'],
                ['length' => 'length:5'],
                'getError'
            ],
            "Length error vE067 - empty test value" => [
                ['length' => 'length'],
                ['length' => 'length'],
                'showDevError'
            ],
            "Length error vE068 - invalid modification" => [
                ['length' => 'length'],
                ['length' => 'length:f:5'],
                'showDevError'
            ],
            "Length error vE069 - invalid value" => [
                ['length' => 'length'],
                ['length' => 'length:5'],
                'showDevError'
            ],
            "Max error vE070 - invalid value" => [
                ['max' => '10'],
                ['max' => 'max:5'],
                'getError'
            ],
            "Max error vE071 - empty test value" => [
                ['max' => '5'],
                ['max' => 'max'],
                'showDevError'
            ],
            "Max error vE072 - unknown type" => [
                ['max' => 'unknown-type'],
                ['max' => 'max:5'],
                'showDevError'
            ],
            "Max error vE073 - not comparable type" => [
                ['max' => '23:59:59'],
                ['max' => 'max:2026-12-31'],
                'showDevError'
            ],
            "Max error vE074 - invalid value" => [
                ['max' => '10'],
                ['max' => 'max:5'],
                'showDevError'
            ],
            "Min error vE075 - invalid value" => [
                ['min' => '5'],
                ['min' => 'min:10'],
                'getError'
            ],
            "Min error vE076 - empty test value" => [
                ['min' => '5'],
                ['min' => 'min'],
                'showDevError'
            ],
            "Min error vE077 - unknown type" => [
                ['min' => 'unknown-type'],
                ['min' => 'min:5'],
                'showDevError'
            ],
            "Min error vE078 - not comparable type" => [
                ['min' => '23:59:59'],
                ['min' => 'min:2026-12-31'],
                'showDevError'
            ],
            "Min error vE079 - invalid value" => [
                ['min' => '5'],
                ['min' => 'min:10'],
                'showDevError'
            ],
            "MaxLength error vE080 - invalid value" => [
                ['maxlength' => 'string'],
                ['maxlength' => 'maxlength:5'],
                'getError'
            ],
            "MaxLength error vE081 - empty test value" => [
                ['maxlength' => 'string'],
                ['maxlength' => 'maxlength'],
                'showDevError'
            ],
            "MaxLength error vE082 - invalid modification" => [
                ['maxlength' => 'string'],
                ['maxlength' => 'maxlength:h:15'],
                'showDevError'
            ],
            "MaxLength error vE083 - invalid value" => [
                ['maxlength' => 'string'],
                ['maxlength' => 'maxlength:5'],
                'showDevError'
            ],
            "MinLength error vE084 - invalid value" => [
                ['minlength' => 'string'],
                ['minlength' => 'minlength:10'],
                'getError'
            ],
            "MinLength error vE085 - empty test value" => [
                ['minlength' => 'string'],
                ['minlength' => 'minlength'],
                'showDevError'
            ],
            "MinLength error vE086 - invalid modification" => [
                ['minlength' => 'string'],
                ['minlength' => 'minlength:h:4'],
                'showDevError'
            ],
            "MinLength error vE087 - invalid value" => [
                ['minlength' => 'string'],
                ['minlength' => 'minlength:10'],
                'showDevError'
            ],
            "MaxWords error vE088 - invalid value" => [
                ['maxwords' => 'word word word word'],
                ['maxwords' => 'maxwords:3'],
                'getError'
            ],
            "MaxWords error vE089 - empty test value" => [
                ['maxwords' => 'string'],
                ['maxwords' => 'maxwords'],
                'showDevError'
            ],
            "MaxWords error vE090 - invalid value" => [
                ['maxwords' => 'word word word word'],
                ['maxwords' => 'maxwords:3'],
                'showDevError'
            ],
            "MinWords error vE091 - invalid value" => [
                ['minwords' => 'word'],
                ['minwords' => 'minwords:3'],
                'getError'
            ],
            "MinWords error vE092 - empty test value" => [
                ['minwords' => 'string'],
                ['minwords' => 'minwords'],
                'showDevError'
            ],
            "MinWords error vE093 - invalid value" => [
                ['minwords' => 'word'],
                ['minwords' => 'minwords:3'],
                'showDevError'
            ],
            "Month error vE094 - invalid value" => [
                ['month' => 'word'],
                ['month' => 'month'],
                'getError'
            ],
            "Month error vE095 - invalid value" => [
                ['month' => 'word'],
                ['month' => 'month'],
                'showDevError'
            ],
            "Nenum error vE028 - invalid value" => [
                ['nenum' => 'nenum'],
                ['nenum' => 'nenum:nenum'],
                'getError'
            ],
            "Nenum error vE029 - empty test value" => [
                ['nenum' => 'nenum'],
                ['nenum' => 'nenum'],
                'showDevError'
            ],
            "Nenum error vE030 - invalid modification" => [
                ['nenum' => 'nenum'],
                ['nenum' => 'nenum:e:enum'],
                'showDevError'
            ],
            "Nenum error vE031 - invalid value" => [
                ['nenum' => 'nenum'],
                ['nenum' => 'nenum:nenum'],
                'showDevError'
            ],
            "Nequal error vE035 - invalid value" => [
                ['nequal' => 'nequal'],
                ['nequal' => 'nequal:nequal'],
                'getError'
            ],
            "Nequal error vE036 - empty test value" => [
                ['nequal' => 'nequal'],
                ['nequal' => 'nequal'],
                'showDevError'
            ],
            "Nequal error vE037 - invalid value" => [
                ['nequal' => 'nequal'],
                ['nequal' => 'nequal:nequal'],
                'showDevError'
            ],
            "Number error vE096 - invalid value" => [
                ['number' => 'value'],
                ['number' => 'number'],
                'getError'
            ],
            "Number error vE097 - invalid modification" => [
                ['number' => '80'],
                ['number' => 'number:e'],
                'showDevError'
            ],
            "Number error vE098 - fail convert" => [
                ['number' => '80e124-5'],
                ['number' => 'number:o'],
                'showDevError'
            ],
            "Number error vE099 - too large to be convert" => [
                ['number' => '80e1245'],
                ['number' => 'number:o,f'],
                'showDevError'
            ],
            "Number error vE100 - too large to be convert" => [
                ['number' => '80e1245'],
                ['number' => 'number:o,i'],
                'showDevError'
            ],
            "Number error vE101 - invalid value" => [
                ['number' => '80e'],
                ['number' => 'number:f'],
                'showDevError'
            ],
            "Number error vE102 - invalid value" => [
                ['number' => '80.5'],
                ['number' => 'number:i'],
                'showDevError'
            ],
            "Number error vE103 - invalid value" => [
                ['number' => 'value'],
                ['number' => 'number'],
                'showDevError'
            ],
            "Password error vE104 - invalid value" => [
                ['password' => 'value'],
                ['password' => 'password'],
                'getError'
            ],
            "Password error vE105 - invalid value" => [
                ['password' => 'value'],
                ['password' => 'password'],
                'showDevError'
            ],
            "Password error vE106 - invalid modification" => [
                ['password' => 'Value123'],
                ['password' => 'password:a'],
                'showDevError'
            ],
            "Regex error vE107 - invalid value" => [
                ['regex' => 'a1-b2'],
                ['regex' => 'regex:^[a-z]+$'],
                'getError'
            ],
            "Regex error vE108 - empty test value" => [
                ['regex' => 'a'],
                ['regex' => 'regex'],
                'showDevError'
            ],
            "Regex error vE110 - invalid test value" => [
                ['regex' => 'a'],
                ['regex' => 'regex:^[a-z+$'],
                'showDevError'
            ],
            "Regex error vE110 - invalid value" => [
                ['regex' => 'a1-b2'],
                ['regex' => 'regex:^[a-z]+$'],
                'showDevError'
            ],
            "Required error vE112 - invalid value" => [
                ['required' => ''],
                ['required' => 'required'],
                'showDevError'
            ],
            "Required error vE112 - invalid value" => [
                ['required' => ''],
                ['required' => 'required'],
                'showDevError'
            ],
            "Required error vE112 - INTERNAL_EMPTY value" => [
                ['required' => Globals::INTERNAL_EMPTY],
                ['required' => 'required'],
                'showDevError'
            ],
            "Tel error vE113 - invalid value" => [
                ['tel' => 'invalid-value'],
                ['tel' => 'tel'],
                'getError'
            ],
            "Time error vE116 - invalid value" => [
                ['time' => 'invalid-value'],
                ['time' => 'time'],
                'getError'
            ],
            "Time error vE117 - invalid value" => [
                ['time' => 'invalid-value'],
                ['time' => 'time'],
                'showDevError'
            ],
            "Url error vE118 - invalid value" => [
                ['url' => 'invalid-value'],
                ['url' => 'url'],
                'getError'
            ],
            "Url error vE119 - invalid value" => [
                ['url' => 'invalid-value'],
                ['url' => 'url'],
                'showDevError'
            ],
            "Url error vE120 - invalid modification" => [
                ['url' => 'https://google.com'],
                ['url' => 'url:a'],
                'showDevError'
            ],
            "Url error vE121 - invalid value" => [
                ['url' => 'https://google/form-generator-invalid'],
                ['url' => 'url:d'],
                'showDevError'
            ],
            "Week error vE122 - invalid value" => [
                ['week' => 'invalid-value'],
                ['week' => 'week'],
                'getError'
            ],
            "Week error vE123 - invalid value" => [
                ['week' => 'invalid-value'],
                ['week' => 'week'],
                'showDevError'
            ],
            "All variants of entering rules during validation" => [
                [
                    'string' => 'word1',
                    'string2' => 'word1',
                    'string3' => 'minLenghtIs12Symbols',
                    'number' => '13',
                    'string4' => 'minLenghtIs12Symbols',
                    'string5' => 'minLenghtIs12Symbols',
                    'string6' => 'minLenghtIs12Symbols',
                    'email' => 'john.doe@example.com'
                ],
                [
                    'string' => ['en' => ['word1', 'word2', 'word3', 'word4']],
                    'string2' => ['string:h', 'req', 'en' => ['word1', 'word2', 'word3', 'word4'], ['minl:c' => '12']],
                    'string3' => ['string' => true, 'req' => 'true', 'minl' => ['c' => '12']],
                    'number' => ['string', 'req', 'min' => '12'],
                    'string4' => ['string', 'req', 'minl:c:12'],
                    'string5' => ['string' => true, 'req' => 'true', 'minl' => ['c' => ['12', '14']]],
                    'string6' => ['string', 'req', 'minl:c' => '12'],
                    'email' => 'email;req;   minl:c:12;'
                ]
            ],
            'All valid recursive' => [
                [
                    'alpha' => ['TestString', 'TestString'],
                    'alphaNumber' => ['Test123', 'Test123'],
                    'boolean' => [true, "false"],
                    'boolean2' => ['1', '0'],
                    'color' => ['#ff5733', '#85ff33'],
                    'date' => ['2023-10-05', '2023-10-05'],
                    'dateTime' => ['2023-10-05 14:30:00', '2023-10-05 14:30:00'],
                    'email' => ['john.doe@example.com', ['john.doe@example.com', 'john.doe@example.com']],
                    'enum' => ['option1', 'option2'],
                    'nenum' => ['not_in_enum', 'not_in_enum2'],
                    'equal' => ['match', 'match'],
                    'nequal' => ['match', 'match'],
                    'ip' => ['192.168.1.1', '192.168.1.1'],
                    'json' => ['{"key": "value"}', '{"key": "value"}'],
                    'length' => ['12345', '12345'],
                    'max-number' => [50.5, 50.5],
                    'max-timestamp' => ['22503680000', '22503680000'],
                    'max-datetime_tz' => ['2026-01-01T00:00:00+00:00', '2026-01-01T00:00:00+00:00'],
                    'max-datetime_sec' => ['2026-01-01T00:00:00', '2026-01-01T00:00:00'],
                    'max-datetime' => ['2026-01-01T00:00', '2026-01-01T00:00'],
                    'max-date' => ['2026-01-01', '2026-01-01'],
                    'max-time_sec' => ['00:00:00', '00:00:00'],
                    'max-time' => ['00:00', ['00:00', '00:00']],
                    'max-week' => ['2026-W01', '2026-W01'],
                    'max-year_month' => ['2026-01', '2026-01'],
                    'max-year' => ['2026', '2026'],
                    'min-number' => [50.5, 50.5],
                    'min-timestamp' => ['22503680000', '22503680000'],
                    'min-datetime_tz' => ['2025-01-01T00:00:00+00:00', '2025-01-01T00:00:00+00:00'],
                    'min-datetime_sec' => ['2025-01-01T00:00:00', '2025-01-01T00:00:00'],
                    'min-datetime' => ['2025-01-01T00:00', '2025-01-01T00:00'],
                    'min-date' => ['2025-01-01', '2025-01-01'],
                    'min-time_sec' => ['23:58:59', '23:58:59'],
                    'min-time' => ['23:58', '23:58'],
                    'min-week' => ['2025-W01', '2025-W01'],
                    'min-year_month' => ['2025-01', [['2025-01', '2025-01'], '2025-01']],
                    'min-year' => ['2025', '2025'],
                    'maxLength' => ['short', 'short'],
                    'minLength' => ['longerstring', 'longerstring'],
                    'maxWords' => ['one two three', 'one two three'],
                    'minWords' => ['one two three four five', 'one two three four five'],
                    'month' => ['2023-10', '2023-10'],
                    'number' => [42, 42],
                    'password' => ['P@ssw0rd!', 'P@ssw0rd!'],
                    'regex' => ['Abc123', 'Abc123'],
                    'required' => ['I am required', 'I am required'],
                    'string' => ['Just a string', 'Just a string'],
                    'tel' => ['+421 912 345 678', '0912345678'],
                    'time' => ['14:30:00', '14:30:00'],
                    'url' => ['https://www.example.com', 'https://www.example.com'],
                    'week' => ['2023-W40', '2023-W40'],
                ],
                [
                    'alpha' => 'alpha',
                    'alphaNumber' => 'alphaNumber',
                    'boolean' => 'boolean',
                    'boolean2' => 'boolean',
                    'color' => 'color',
                    'date' => 'date',
                    'dateTime' => 'dateTime',
                    'email' => 'email',
                    'enum' => 'enum:option1,option2,option3',
                    'nenum' => 'nenum:option1,option2,option3',
                    'equal' => 'equal:match',
                    'nequal' => 'nequal:notmatch',
                    'extension' => 'extension:txt',
                    'ip' => 'ip',
                    'json' => 'json',
                    'length' => 'length:5',
                    'max-number' => 'max:100',
                    'max-timestamp' => 'max:32503680000',
                    'max-datetime_tz' => 'max:2026-01-01T12:30:45+00:00',
                    'max-datetime_sec' => 'max:2026-01-01T12:30:45',
                    'max-datetime' => 'max:2026-01-01T12:30',
                    'max-date' => 'max:2026-12-31',
                    'max-time_sec' => 'max:23:59:59',
                    'max-time' => 'max:23:59',
                    'max-week' => 'max:2026-W52',
                    'max-year_month' => 'max:2026-12',
                    'max-year' => 'max:2030',
                    'min-number' => 'min:40',
                    'min-timestamp' => 'min:22500000000',
                    'min-datetime_tz' => 'min:2024-12-31T12:30:45+00:00',
                    'min-datetime_sec' => 'min:2024-12-31T12:30:45',
                    'min-datetime' => 'min:2024-12-31T12:30',
                    'min-date' => 'min:2024-12-30',
                    'min-time_sec' => 'min:21:58:59',
                    'min-time' => 'min:21:58',
                    'min-week' => 'min:2024-W52',
                    'min-year_month' => 'min:2024-12',
                    'min-year' => 'min:2024',
                    'maxLength' => 'maxLength:10',
                    'minLength' => 'minLength:5',
                    'maxWords' => 'maxWords:5',
                    'minWords' => 'minWords:3',
                    'month' => 'month',
                    'number' => 'number',
                    'password' => 'password',
                    'regex' => 'regex:/^[a-z0-9]+$/i',
                    'required' => 'required',
                    'string' => 'string',
                    'tel' => 'tel',
                    'time' => 'time',
                    'url' => 'url',
                    'week' => 'week',
                ]
            ],
            "All format value" => [
                [
                    "string" => "string",
                    'empty' => '',
                    "integer" => "52",
                    "float" => "3.14159265359",
                    "round" => "3.14159265359",
                    'timestamp' => '22503680000',
                    'datetime_tz' => '2026-01-01T00:00:00+00:00',
                    'datetime_sec' => '2026-01-01T00:00:00',
                    'datetime' => '2026-01-01T00:00',
                    'date' => '2026-01-01',
                    'time_sec' => '00:00:00',
                    'time' => '00:00',
                    'week' => '2026-W01',
                    'year_month' => '2026-01',
                    'year' => '2026',
                    'boolean' => "true",
                ],
                [
                    "string" => "format:s",
                    'empty' => 'format:s',
                    "integer" => "format:i",
                    "float" => "format:f",
                    "round" => "format:r:5",
                    'timestamp' => 'format:dt',
                    'datetime_tz' => 'format:dt',
                    'datetime_sec' => 'format:dt',
                    'datetime' => 'format:dt',
                    'date' => 'format:dt',
                    'time_sec' => 'format:dt',
                    'time' => 'format:dt',
                    'week' => 'format:dt',
                    'year_month' => 'format:dt',
                    'year' => 'format:dt',
                    'boolean' => "format:b"
                ]
            ],
            "Ignor all array format" => [
                [
                    "string" => ["string"],
                    "integer" => ["52"],
                    "float" => ["3.14159265359"],
                    "round" => ["3.14159265359"],
                    'timestamp' => ['22503680000'],
                    'datetime_tz' => ['2026-01-01T00:00:00+00:00'],
                    'datetime_sec' => ['2026-01-01T00:00:00'],
                    'datetime' => ['2026-01-01T00:00'],
                    'date' => ['2026-01-01'],
                    'time_sec' => ['00:00:00'],
                    'time' => ['00:00'],
                    'week' => ['2026-W01'],
                    'year_month' => ['2026-01'],
                    'year' => ['2026'],
                    'boolean' => ["true"],
                ],
                [
                    "string" => "format:s",
                    "integer" => "format:i",
                    "float" => "format:f",
                    "round" => "format:r:5",
                    'timestamp' => 'format:dt',
                    'datetime_tz' => 'format:dt',
                    'datetime_sec' => 'format:dt',
                    'datetime' => 'format:dt',
                    'date' => 'format:dt',
                    'time_sec' => 'format:dt',
                    'time' => 'format:dt',
                    'week' => 'format:dt',
                    'year_month' => 'format:dt',
                    'year' => 'format:dt',
                    'boolean' => "format:b"
                ]
            ],
            "Format error vE124 - empty test modification" => [
                ['string' => 'string'],
                ['string' => 'format'],
                'showDevError'
            ],
            "Format error vE125 - invalid modification" => [
                ['string' => 'string'],
                ['string' => 'format:h'],
                'showDevError'
            ],
            "Format error vE126 - too large to be convert" => [
                ['round' => '100e10000'],
                ['round' => 'format:r'],
                'showDevError'
            ],
            "Format error vE127 - too large to be convert" => [
                ['integer' => '100e10000'],
                ['integer' => 'format:i'],
                'showDevError'
            ],
            "Format error vE128 - too large to be convert" => [
                ['float' => '100e10000'],
                ['float' => 'format:f'],
                'showDevError'
            ],
            "Format error vE129 - invalid value " => [
                ['datetime' => 'invalid-value'],
                ['datetime' => 'format:dt'],
                'showDevError'
            ],
            "Format error vE130 - invalid value " => [
                ['boolean' => 'invalid-value'],
                ['boolean' => 'format:b'],
                'showDevError'
            ],
            "Valid data witch __CONFIG__" => [
                [
                    'string' => '<p>Ahoj</p>',
                    'number' => '18'
                ],
                [
                    '__CONFIG__' => 'strip_tags:f; h:f',
                    'string' => 'minl:3',
                    'number' => 'max:20'
                ],
            ],
            "__CONFIG__ error vE132 - empty test value" => [
                [
                    'string' => '<p>Ahoj</p>',
                    'number' => '18'
                ],
                [
                    '__CONFIG__' => 's; h',
                    'string' => 'minl:3',
                    'number' => 'max:20'
                ],
                'showDevError'
            ],
            "__CONFIG__ error vE133 - empty test value" => [
                [
                    'string' => '<p>Ahoj</p>',
                    'number' => '18'
                ],
                [
                    '__CONFIG__' => 'j:f; h:t',
                    'string' => 'minl:3',
                    'number' => 'max:20'
                ],
                'showDevError'
            ],
        ];
    }
}
