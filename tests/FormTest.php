<?php

declare(strict_types=1);

namespace FormGenerator\Tests;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Validation\ValidationResult;
use PHPUnit\Framework\TestCase;
use FormGenerator\Form;
use FormGenerator\DataProvider\FormDataProvider;
use FormGenerator\DataProvider\JsonDataProvider;
use FormGenerator\Components\HTML;
use FormGenerator\Components\Label;
use FormGenerator\Components\Fieldset;
use FormGenerator\Components\Legend;
use FormGenerator\Components\Inputs\Text;
use FormGenerator\Components\Inputs\Password;
use FormGenerator\Components\Inputs\Search;
use FormGenerator\Components\Inputs\Email;
use FormGenerator\Components\Inputs\Tel;
use FormGenerator\Components\Inputs\Url;
use FormGenerator\Components\Inputs\Number;
use FormGenerator\Components\Inputs\Range;
use FormGenerator\Components\Inputs\Date;
use FormGenerator\Components\Inputs\Time;
use FormGenerator\Components\Inputs\DateTime;
use FormGenerator\Components\Inputs\Month;
use FormGenerator\Components\Inputs\Week;
use FormGenerator\Components\Inputs\CheckBox;
use FormGenerator\Components\Inputs\Radio;
use FormGenerator\Components\Inputs\File;
use FormGenerator\Components\Inputs\Hidden;
use FormGenerator\Components\Inputs\Color;
use FormGenerator\Components\Inputs\Button;
use FormGenerator\Components\Inputs\Image;
use FormGenerator\Components\Inputs\Submit;
use FormGenerator\Components\Inputs\Reset;
use FormGenerator\Components\Inputs\TextArea;
use FormGenerator\Components\Inputs\Select;
use FormGenerator\Config\Globals;
use FormGenerator\Config\Config;
use FormGenerator\ErrorHandling\FormErrorHandler;
use FormGenerator\ErrorHandling\FormGeneratorException;
use FormGenerator\Validation;
use FormGenerator\Validation\Rules\File as FileRule;

#[\PHPUnit\Framework\Attributes\CoversClass(Config::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]

#[\PHPUnit\Framework\Attributes\CoversClass(ValidationResult::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(FormDataProvider::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(JsonDataProvider::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(FormErrorHandler::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(FormGeneratorException::class)]


#[\PHPUnit\Framework\Attributes\CoversClass(Validation::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(FileRule::class)]

#[\PHPUnit\Framework\Attributes\CoversClass(BasicComponent::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(HTML::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Label::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Fieldset::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Legend::class)]

#[\PHPUnit\Framework\Attributes\CoversClass(Text::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Password::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Search::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Email::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Tel::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Url::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Number::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Range::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Date::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Time::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DateTime::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Month::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Week::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(CheckBox::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Radio::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(File::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Hidden::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Color::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Button::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Image::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Submit::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Reset::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(TextArea::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Select::class)]

#[\PHPUnit\Framework\Attributes\CoversClass(Form::class)]
final class FormTest extends TestCase
{
    private Form $form;
    private Form $CAPTCHAform;

    protected function setUp(): void
    {
        parent::setUp();
        $this->form = new Form('action', 'FormID');
        $this->CAPTCHAform = new Form('action', 'CAPTCHAFormID');
        $this->CAPTCHAform->captcha();
    }

    public function testFormtsIsInstanceOfForm(): void
    {
        $this->assertInstanceOf(Form::class, $this->form);

        $this->assertInstanceOf(Form::class, Form::new('action', 'FormID'));
    }

    public function testNewFormWithEmptyFormID(): void
    {
        $form = Form::new('action', '');

        $this->assertInstanceOf(Form::class, $form);

        $html = $form->getHTML();
        $this->assertMatchesRegularExpression(
            '~<form[^>]*action="action"[^>]*id="form[a-f0-9]+".*?</form>~s',
            $html
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('idProvider')]
    public function testIdAttribute(string $value, string $expected): void
    {
        $html = $this->form->id($value)->getHTML();
        $this->assertStringContainsString('id="' . $expected . '"', $html);
    }

    public static function idProvider(): array
    {
        return [
            'simple name' => [
                'username',
                'username',
            ],
            'array syntax' => [
                'user[name]',
                'user_name',
            ],
            'nested array' => [
                'user[address][street]',
                'user_address_street',
            ],
            'array input' => [
                'items[]',
                'items_0',
            ],
            'starts with number' => [
                '123name',
                'id_123name',
            ],
            'special chars' => [
                'user@name!',
                'user_name_',
            ],
            'complex' => [
                '1user[data][value][]',
                'id_1user_data_value_0',
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nameProvider')]
    public function testNameAttribute(string $value, string $expected): void
    {
        $html = $this->form->name($value)->getHTML();
        $this->assertStringContainsString('name="' . $expected . '"', $html);
    }

    public static function nameProvider(): array
    {
        return [
            'simple name' => ['name', 'name'],
            'complex name' => ['user[name]', 'user[name]'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('classProvider')]
    public function testClassAttribute(array|string $value, string $expected): void
    {
        $html = $this->form->class($value)->getHTML();
        if (is_array($value)) {
            $value = implode('', $value);
        }
        if (!empty(trim($value))) {
            $this->assertStringContainsString('class="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('class=', $html);
        }
    }

    public static function classProvider(): array
    {
        return [
            'single class' => ['class', 'class'],
            'multiple classes' => [['class1', 'class2'], 'class1 class2'],
            'null' => ['  ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('multipleClassProvider')]
    public function testMultipleClassAttribute(array $value, string $expected): void
    {
        foreach ($value as $key => $val) {
            $html = $this->form->class($val)->getHTML();
            if (is_array($val)) {
                $value[$key] = implode(' ', $val);
            }
        }
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            $this->assertStringContainsString('class="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('class=', $html);
        }
    }

    public static function multipleClassProvider(): array
    {
        return [
            'single class' => [['class1', 'class2'], 'class1 class2'],
            'multiple classes' => [[['class1', 'class2'], ['class3', 'class4']], 'class1 class2 class3 class4'],
        ];
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('attributesProvider')]
    public function testSetAttributesAttribute(array $attributes, array $expectedContains): void
    {
        $html = $this->form->setAttributes($attributes)->getHTML();

        foreach ($expectedContains as $str) {
            $this->assertStringContainsString($str, $html);
        }
    }

    public static function attributesProvider(): array
    {
        return [
            'style as string' => [
                ['style' => 'color: red;'],
                ['style=" color: red;"']
            ],
            'style as array' => [
                ['style' => ['color: red;', 'font-weight: bold;']],
                ['style=" color: red; font-weight: bold;"']
            ],
            'class as string' => [
                ['class' => 'btn primary'],
                ['class=" btn primary"']
            ],
            'class as array' => [
                ['class' => ['btn', 'primary']],
                ['class=" btn primary"']
            ],
            'string attribute' => [
                ['title' => 'Hello "world" & friends'],
                ['title="Hello &quot;world&quot; &amp; friends"']
            ],
            'non-string attribute' => [
                ['data-count' => 5, 'data-flag' => true],
                ['data-count="5"', 'data-flag']
            ],
        ];
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('styleProvider')]
    public function testStyleAttribute(array|string $value, string $expected): void
    {
        $html = $this->form->style($value)->getHTML();
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            $this->assertStringContainsString('style="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('style=', $html);
        }
    }

    public static function styleProvider(): array
    {
        return [
            'single style' => ['style', 'style;'],
            'multiple styles' => [['style1', 'style2;'], 'style1; style2;'],
            'null styles' => ['     ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('multipleStyleProvider')]
    public function testMultipleStyleAttribute(array $value, string $expected): void
    {
        foreach ($value as $key => $val) {
            $html = $this->form->style($val)->getHTML();
            if (is_array($val)) {
                $value[$key] = implode(' ', $val);
            }
        }
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            $this->assertStringContainsString('style="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('style=', $html);
        }
    }

    public static function multipleStyleProvider(): array
    {
        return [
            'single class' => [['style1', 'style2'], 'style1; style2;'],
            'multiple classes' => [[['style1', 'style2'], ['style3', 'style4']], 'style1; style2; style3; style4;'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('relProvider')]
    public function testRelAttribute(array|string $value, string $expected): void
    {
        $html = $this->form->rel($value)->getHTML();
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            $this->assertStringContainsString('rel="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('rel=', $html);
        }
    }

    public static function relProvider(): array
    {
        return [
            'single rel' => ['nofollow', 'nofollow'],
            'null rel' => ['   ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('methodProvider')]
    public function testMethodAttribute(array|string $value, string $expected): void
    {
        $html = $this->form->method($value)->getHTML();
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            $this->assertStringContainsString('method="' . $expected . '"', $html);
        } else {
            $this->assertStringContainsString('method="POST"', $html);
        }
    }

    public static function methodProvider(): array
    {
        return [
            'get method' => ['get', 'GET'],
            'post method' => ['post', 'POST'],
            'null method' => ['   ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('actionProvider')]
    public function testActionAttribute(array|string $value, string $expected): void
    {
        $html = $this->form->action($value)->getHTML();
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            $this->assertStringContainsString('action="' . $expected . '"', $html);
        } else {
            $this->assertStringContainsString('action="action"', $html);
        }
    }

    public static function actionProvider(): array
    {
        return [
            'absolute URL' => ['https://example.com/submit', 'https://example.com/submit'],
            'relative URL' => ['/form-handler', '/form-handler'],
            'empty action' => ['   ', '']
        ];
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('enctypeProvider')]
    public function testEnctypeAttribute(array|string $value, string $expected): void
    {
        $html = $this->form->enctype($value)->getHTML();
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            $this->assertStringContainsString('enctype="' . $expected . '"', $html);
        } else {
            $this->assertStringContainsString('enctype="application/x-www-form-urlencoded"', $html);
        }
    }

    public static function enctypeProvider(): array
    {
        return [
            'application shorthand' => ['application', 'application/x-www-form-urlencoded'],
            'application full' => ['application/x-www-form-urlencoded', 'application/x-www-form-urlencoded'],
            'multipart shorthand' => ['multipart', 'multipart/form-data'],
            'multipart full' => ['multipart/form-data', 'multipart/form-data'],
            'plain shorthand' => ['plain', 'text/plain'],
            'plain full' => ['text/plain', 'text/plain'],
            'custom enctype' => ['custom/type', 'custom/type'],
            'empty enctype' => ['   ', '']
        ];
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('targetProvider')]
    public function testTargetAttribute(array|string $value, string $expected): void
    {
        $html = $this->form->target($value)->getHTML();
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            $this->assertStringContainsString('target="' . $expected . '"', $html);
        } else {
            $this->assertStringContainsString('target="_self"', $html);
        }
    }

    public static function targetProvider(): array
    {
        return [
            'self target'   => ['_self', '_self'],
            'blank target'  => ['_blank', '_blank'],
            'parent target' => ['_parent', '_parent'],
            'top target'    => ['_top', '_top'],
            'custom iframe' => ['myFrame', 'myFrame'],
            'empty target'  => ['   ', '']
        ];
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('autocompleteProvider')]
    public function testAutocompleteAttribute(bool|string $value, string $expected): void
    {
        $html = $this->form->autocomplete($value)->getHTML();

        if (is_bool($value)) {
            $value = $value ? 'on' : 'off';
        }

        if (!empty(trim((string)$value))) {
            $this->assertStringContainsString('autocomplete="' . $expected . '"', $html);
        } else {
            $this->assertStringContainsString('autocomplete="on"', $html);
        }
    }

    public static function autocompleteProvider(): array
    {
        return [
            'true boolean'  => [true, 'on'],
            'false boolean' => [false, 'off'],
            'string on'     => ['on', 'on'],
            'string off'    => ['off', 'off'],
            'mixed case on' => ['On', 'on'],
            'mixed case off' => ['OFF', 'off'],
            'invalid string' => ['maybe', 'on'],
            'empty string'  => ['   ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('novalidateProvider')]
    public function testNovalidateAttribute(bool $value, bool $expected): void
    {
        if ($value) {
            $html = $this->form->novalidate()->getHTML();
        } else {
            $html = $this->form->getHTML();
        }

        if ($expected) {
            $this->assertStringContainsString('novalidate', $html);
        } else {
            $this->assertStringNotContainsString('novalidate', $html);
        }
    }

    public static function novalidateProvider(): array
    {
        return [
            'enabled novalidate'  => [true, true],
            'disabled novalidate' => [false, false]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('acceptCharsetProvider')]
    public function testAcceptCharsetAttribute(string $value, string $expected): void
    {
        $html = $this->form->acceptCharset($value)->getHTML();

        if (!empty(trim($value))) {
            $this->assertStringContainsString('accept-charset="' . $expected . '"', $html);
        } else {
            $this->assertStringContainsString('accept-charset="UTF-8"', $html);
        }
    }
    public static function acceptCharsetProvider(): array
    {
        return [
            'utf-8 charset'        => ['UTF-8', 'UTF-8'],
            'iso charset'          => ['ISO-8859-1', 'ISO-8859-1'],
            'multiple charsets'    => ['UTF-8, ISO-8859-1', 'UTF-8'],
            'lowercase charset'    => ['utf-8', 'UTF-8'],
        ];
    }

    public function testRefreshProtected(): void
    {
        $html = $this->form->refreshProtection()->getHTML();

        $htmlNormalized = preg_replace('/\s+/', ' ', $html);
        $scriptNormalized = preg_replace(
            '/\s+/',
            ' ',
            '<script> 
                const form = document.getElementById("FormID"); 
                form.addEventListener("submit", function () { 
                    if (window.history.replaceState) { 
                        window.history.replaceState(null, null, window.location.href); } });
            </script>'
        );

        $this->assertStringContainsString(
            $scriptNormalized,
            $htmlNormalized
        );
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('captchaInitProvider')]
    public function testCaptchaKeyStaticAssignment(
        string $sitekey,
        string $projectId,
        string $credentials,
        ?string $expectedSite,
        ?string $expectedProject,
        ?string $expectedCredentials,
        bool|string $expectedResult,
    ): void {

        putenv('FG_RECAPTCHA_SITEKEY=' . ($sitekey ?? ''));
        putenv('FG_RECAPTCHA_PROJECT_ID=' . ($projectId ?? ''));
        putenv('FG_GOOGLE_APPLICATION_CREDENTIALS=' . ($credentials ?? ''));
        putenv('FG_DEFAULT_GET_DEV_ERRORS=false');



        $normalize = static fn($val) => (trim((string)$val) === '' ? null : $val);

        if ($expectedResult === 'showError') {
            putenv('FG_DEFAULT_GET_DEV_ERRORS=true');
            putenv('FG_DEFAULT_SHOW_DEV_ERRORS=true');

            $pattern = "/<div class='error'"
                . "style='color:red;font-weight:bold;"
                . "padding:10px;"
                . "border-radius:5px;"
                . "background-color:#ffe6e6;"
                . "border: solid 1px red;'"
                . ">(.*?)<\/div>/s";

            $result = Form::captchaInit($projectId, $sitekey, $credentials);
            $this->expectOutputRegex(
                $pattern
            );
        } else {
            $result = Form::captchaInit($projectId, $sitekey, $credentials);
            $this->assertSame(
                $expectedResult,
                $result,
                sprintf(
                    'Expected captchaInit() to return %s, got %s',
                    var_export($expectedResult, true),
                    var_export($result, true)
                )
            );

            $this->assertSame(
                $expectedSite,
                $normalize(getenv('FG_RECAPTCHA_SITEKEY')),
                'Sitekey value does not match expected'
            );

            $this->assertSame(
                $expectedProject,
                $normalize(getenv('FG_RECAPTCHA_PROJECT_ID')),
                'ProjectId value does not match expected'
            );

            $this->assertSame(
                $expectedCredentials,
                $normalize(getenv('FG_GOOGLE_APPLICATION_CREDENTIALS')),
                'Credentials path does not match expected'
            );
        }
    }

    public static function captchaInitProvider(): array
    {
        return [
            'valid keys (all present)' => [
                'SITE123',
                'PROJECT789',
                '/path/to/credentials.json',
                'SITE123',
                'PROJECT789',
                '/path/to/credentials.json',
                true
            ],
            'empty sitekey' => [
                '   ',
                'PROJECT789',
                '/path/to/credentials.json',
                null,
                'PROJECT789',
                '/path/to/credentials.json',
                'showError'
            ],
            'empty projectId' => [
                'SITE123',
                '   ',
                '/path/to/credentials.json',
                'SITE123',
                null,
                '/path/to/credentials.json',
                'showError'
            ],
            'empty credentials' => [
                'SITE123',
                'PROJECT789',
                '   ',
                'SITE123',
                'PROJECT789',
                null,
                true
            ],
            'all empty' => [
                '   ',
                '   ',
                '   ',
                null,
                null,
                null,
                'showError'
            ],
            'developer mode is null and empty keys Error fE002' => [
                '',
                'PROJECT789',
                '/path/to/credentials.json',
                'SITE123',
                'PROJECT789',
                '/path/to/credentials.json',
                'showError',
            ],
        ];
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('captchaProvider')]
    public function testCaptchaMethod(?string $action, string $expectedAction)
    {
        $this->form->captcha($action);

        $reflection = new \ReflectionClass($this->form);

        $captcha = $reflection->getProperty('captcha')->getValue($this->form);
        $this->assertTrue($captcha);

        $method = $reflection->getProperty('method')->getValue($this->form);
        $this->assertEquals('POST', $method);

        $captchaAction = $reflection->getProperty('captchaAction')->getValue($this->form);
        $this->assertEquals($expectedAction, $captchaAction);

        $sitekey = $reflection->getProperty('sitekey')->getValue($this->form);
        $this->assertNotEmpty($sitekey);

        $projectId = $reflection->getProperty('projectId')->getValue($this->form);
        $this->assertNotEmpty($projectId);
    }

    public static function captchaProvider(): array
    {
        return [
            'default action' => [null, 'GENERAL'],
            'custom action' => ['LOGIN', 'LOGIN'],
            'action with invalid chars' => ['lo!g@in#', 'login'],
            'empty string' => ['', 'GENERAL'],
        ];
    }

    public function testCaptchaSetsSitekeyAndProjectId()
    {
        $reflection = new \ReflectionClass($this->form);

        $property = $reflection->getProperty('sitekey');
        $property->setValue($this->form, 'siteKey');

        $property = $reflection->getProperty('projectId');
        $property->setValue($this->form, 'projectId');

        $this->form->captcha();

        $sitekey = $reflection->getProperty('sitekey')->getValue($this->form);
        $projectId = $reflection->getProperty('projectId')->getValue($this->form);

        $this->assertNotEmpty($sitekey, 'Sitekey does not get set correctly in captcha() method.');
        $this->assertNotEmpty($projectId, 'ProjectId does not get set correctly in captcha() method.');
    }


    public function testStartAndEndForm(): void
    {
        $message = $this->form->getHTML() ? $this->form->getHTML() : "";
        $this->expectOutputString($message);
        $this->form->start();
        $this->form->end();
    }
    public function testStartAndEndFormWitchCAPTCHA(): void
    {
        $this->form->captcha();
        $message = $this->form->getHTML() ? $this->form->getHTML() : "";
        $this->expectOutputString($message);
        $this->form->start();
        $this->form->end();
    }

    public function testMethodGetForm(): void
    {

        $message = $this->form->getHTML() ? $this->form->getHTML() : "";
        $this->expectOutputString($message);
        $this->form->getForm();
    }
    public function testMethodGetFormWitchCaptcha(): void
    {
        $this->form->captcha();
        $message = $this->form->getHTML() ? $this->form->getHTML() : "";
        $this->expectOutputString($message);
        $this->form->getForm();
    }

    public function testMethodShowHtml(): void
    {

        $message = $this->form->getHTML() ? $this->form->getHTML() : "";

        $html = Globals::toStr(
            preg_replace('/\s*\/?>\s*/', ">\n", $message)
        );

        $html = preg_replace_callback(
            '/<(label|p|button|input|br|hr|img|option|a)[^>]*>.*?<\/\1>\s*/is',
            function (array $m): string {
                return trim(
                    (string) preg_replace('/\s+/', ' ', $m[0])
                ) . "\n";
            },
            $html
        );

        $void = [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr'
        ];
        $indent = 0;
        $pretty = '';

        foreach (explode("\n", Globals::toStr($html)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^<\/[a-z0-9]+/i', $line)) {
                $indent--;
            }
            $pretty .= str_repeat('  ', $indent) . $line . "\n";
            if (preg_match('/^<([a-z0-9]+)(\s[^>]*)?>$/i', $line, $m) && !in_array(strtolower($m[1]), $void)) {
                $indent++;
            }
        }

        $message = '<pre>' . htmlspecialchars($pretty) . '</pre>';

        $this->expectOutputString($message);
        $this->form->showHTML();
    }



    #[\PHPUnit\Framework\Attributes\DataProvider('formElementsProvider')]
    public function testFormMethodsReturnCorrectObjects(
        string $factoryMethod,
        string $expectedType,
        array $parameters
    ): void {
        $this->assertInstanceOf($expectedType, $this->form->$factoryMethod(...$parameters));
    }

    public static function formElementsProvider(): array
    {
        return [
            'Button'   => ['button', Button::class, ['button', 'button']],
            'CheckBox' => ['checkBox', CheckBox::class, ['checkBox']],
            'Color' => ['color', Color::class, ['color']],
            'Date' => ['date', Date::class, ['date']],
            'DateTime' => ['dateTime', DateTime::class, ['dateTime']],
            'Email' => ['email', Email::class, ['email']],
            'File' => ['file', File::class, ['file']],
            'Hidden' => ['hidden', Hidden::class, ['hidden']],
            'Image' => ['image', Image::class, ['image', 'src', 'alt']],
            'Month' => ['month', Month::class, ['month']],
            'Number' => ['number', Number::class, ['number']],
            'Password' => ['password', Password::class, ['password']],
            'Radio' => ['radio', Radio::class, ['radio']],
            'Range' => ['range', Range::class, ['range']],
            'Reset' => ['reset', Reset::class, ['reset']],
            'Search' => ['search', Search::class, ['search']],
            'Select' => ['select', Select::class, ['select']],
            'Submit' => ['submit', Submit::class, ['submit']],
            'Tel' => ['tel', Tel::class, ['tel']],
            'TextArea' => ['textArea', TextArea::class, ['textArea']],
            'Text' => ['text', Text::class, ['text']],
            'Time' => ['time', Time::class, ['time']],
            'Url' => ['url', Url::class, ['url']],
            'Week' => ['week', Week::class, ['week']],
            'EndFieldset' => ['endFieldset', Form::class, []],
            'Fieldset' => ['fieldset', Fieldset::class, []],
            'HTML' => ['HTML', HTML::class, ['<p>HTML</p>']],
            'Label' => ['label', Label::class, ['label', 'for']],
            'Legend' => ['legend', Legend::class, ['legend']]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('multipleCheckBoxesProvider')]
    public function testMultipleCheckBoxes(
        string $name,
        ?array $values,
        ?array $labels,
        ?array $checkedValues,
        bool|string $expectedResult = true
    ): void {

        if ($expectedResult === 'showError') {
            putenv('FG_DEFAULT_GET_DEV_ERRORS=true');
            putenv('FG_DEFAULT_SHOW_DEV_ERRORS=true');
            $pattern = "/<div class='error'"
                . "style='color:red;font-weight:bold;"
                . "padding:10px;"
                . "border-radius:5px;"
                . "background-color:#ffe6e6;"
                . "border: solid 1px red;'"
                . ">(.*?)<\/div>/s";

            $this->form->checkBox($name, $values ?? [], $labels ?? [], $checkedValues ?? []);
            $this->expectOutputRegex(
                $pattern
            );
        } else {
            $this->form->checkBox($name, $values ?? [], $labels ?? [], $checkedValues ?? []);

            $html = $this->form->getHTML() ?? '';

            if ($values === null && $labels === null) {
                $this->assertStringContainsString('<input', $html);
                return;
            }
            foreach ($values as $i => $value) {
                $pattern = '/<input[^>]+name=["\']'
                    . preg_quote("FormID[$name][]", '/')
                    . '["\'][^>]*value=["\']'
                    . preg_quote($value, '/')
                    . '["\']([^>]*)>/';
                $this->assertMatchesRegularExpression($pattern, $html, "Checkbox for value '$value' not found.");

                $isCheckedExpected = in_array($value, $checkedValues ?? []) ? 'checked' : '';
                if ($isCheckedExpected) {
                    $this->assertStringContainsString(
                        'checked',
                        preg_match($pattern, $html, $matches) ? $matches[1] : '',
                        "Checkbox '$value' should be checked."
                    );
                }
            }
        }
    }
    public static function multipleCheckBoxesProvider(): array
    {
        return [
            'multiple checkboxes with some checked' => [
                'hobbies',
                ['reading', 'traveling', 'cooking'],
                ['Reading', 'Traveling', 'Cooking'],
                ['reading', 'cooking'],
            ],
            'multiple checkboxes with all checked' => [
                'fruits',
                ['apple', 'banana', 'cherry'],
                ['Apple', 'Banana', 'Cherry'],
                ['apple', 'banana', 'cherry'],
            ],
            'multiple checkboxes with none checked' => [
                'colors',
                ['red', 'green', 'blue'],
                ['Red', 'Green', 'Blue'],
                [],
            ],
            'multiple checkboxes with null values and labels' => [
                'options',
                null,
                null,
                null,
            ],
            'multiple checkboxes with mismatched values and labels' => [
                'pets',
                ['dog', 'cat'],
                ['Dog'],
                [],
                'showError',
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('multipleRadiosProvider')]
    public function testRadio(
        string $name,
        ?array $values,
        ?array $labels,
        bool|string $expectedResult = true
    ): void {

        if ($expectedResult === 'showError') {
            putenv('FG_DEFAULT_GET_DEV_ERRORS=true');
            putenv('FG_DEFAULT_SHOW_DEV_ERRORS=true');
            $pattern = "/<div class='error'" .
                "style='color:red;font-weight:bold;" .
                "padding:10px;" .
                "border-radius:5px;" .
                "background-color:#ffe6e6;" .
                "border: solid 1px red;'" .
                ">(.*?)<\/div>/s";

            $this->form->radio($name, $values ?? [], $labels ?? []);
            $this->expectOutputRegex($pattern);
        } else {
            $this->form->radio($name, $values ?? [], $labels ?? []);

            $html = $this->form->getHTML() ?? '';

            if ($values === null && $labels === null) {
                $this->assertStringContainsString('<input', $html);
                return;
            }

            foreach ($values as $i => $value) {
                $pattern = '/<input[^>]+name=["\']'
                    . preg_quote("FormID[$name]", '/')
                    . '["\'][^>]*value=["\']'
                    . preg_quote($value, '/')
                    . '["\']([^>]*)>/';
                $this->assertMatchesRegularExpression($pattern, $html, "Radio for value '$value' not found.");

                if ($labels !== null && isset($labels[$i])) {
                    $this->assertStringContainsString(
                        $labels[$i],
                        $html,
                        "Label for radio '$value' not found."
                    );
                }
            }
        }
    }

    public static function multipleRadiosProvider(): array
    {
        return [
            'radio with labels' => [
                'gender',
                ['male', 'female'],
                ['Male', 'Female'],
            ],
            'radio with values only' => [
                'subscription',
                ['basic', 'premium'],
                [],
            ],
            'radio with null values and labels' => [
                'options',
                null,
                null,
            ],
            'radio with mismatched values and labels' => [
                'pets',
                ['dog', 'cat'],
                ['Dog'],
                'showError',
            ],
        ];
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('getDataFromProvider')]
    public function testGetDataFrom(
        string $formID,
        array $session,
        array $server,
        array $post,
        array $validation,
        bool|string $expectedValid,
    ): void {
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
        putenv('FG_DEFAULT_GET_DEV_ERRORS=false');
        putenv('FG_DEFAULT_SHOW_DEV_ERRORS=false');
        $result = Form::getDataFrom(
            $formID,
            $validation,
            null
        );
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertSame($expectedValid, $result->isValid());
    }

    public static function getDataFromProvider(): array
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
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('jsonProvider')]
    public function testJson(
        string|array $json,
        bool|string $expected = true
    ): void {

        Form::captchaInit("projectid", "sitekey");
        $form = Form::new("/submit", "simpleForm");
        $form->captcha();
        $form->text("username")->id("text")->required();
        $form->password("password")->label("password");
        $form->submit("send");
        $form->html("<div>")->class(" div")->name("divName");
        $form->html("<p>p</p>");
        $form->html("</div>");
        $result = Form::json(
            $json,
            false
        );
        if (is_bool($expected)) {
            $this->assertSame($form->getHTML(), $result);
        } else {
            $error = [
                'message' => 'Invalid JSON string.',
                'path' => 'root',
                'id' => 'jE001',
                'code' => 400
            ];
            $true = '';
            foreach ($error as $key => $value) {
                $true .= (string)$key . ': ' . (string)$value . ' | ';
            }
            $this->assertSame($true, $result);
            foreach ($error as $key => $value) {
                $this->assertStringContainsString("$key: $value |", $result);
            }
        }
    }

    public static function jsonProvider(): array
    {
        return [
            'valid Json' => [
                '{
                    "security": true,
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid"
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                            "type": "text",
                            "attributes": {
                                "name": "username",
                                "id": "text",
                                "required":true
                            }
                        },
                        {
                            "type": "password",
                            "attributes": {
                                "name": "password",
                                "label": "password"
                            }
                        },
                        {
                            "type": "submit",
                            "attributes": {
                                "name": "send"
                            }
                        },
                        {
                            "type": "div",
                            "attributes": {
                                "class": "div",
                                "name":"divName"
                            },
                            "children": [
                                {
                                    "type": "p",
                                    "children": ["p"]
                                }
                            ]
                        }
                    ]
                }
                ',
                true
            ],
            'invalid Json Error jE001' => [
                '{
                    "security": true
                    "captcha": {
                        "sitekey": "sitekey",
                        "projectid": "projectid",
                    },
                    "attributes": {
                        "id": "simpleForm",
                        "action": "/submit"
                    },
                    "children": [
                        {
                            "type": "text",
                            "attributes": {
                                "name": "username",
                                "id": "text",
                                "required":true
                            }
                        },
                        {
                            "type": "password",
                            "attributes": {
                                "name": "password",
                                "label": "password"
                            }
                        },
                        {
                            "type": "submit",
                            "attributes": {
                                "name": "send"
                            }
                        },
                        {
                            "type": "div",
                            "attributes": {
                                "class": "div",
                                "name":"divName"
                            },
                            "children": [
                                {
                                    "type": "p",
                                    "children": ["p"]
                                }
                            ]
                        }
                    ]
                }
                ',
                "getError"
            ]
        ];
    }
}
