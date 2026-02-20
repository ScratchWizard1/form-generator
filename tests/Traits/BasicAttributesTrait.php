<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Traits;

use FormGenerator\Components\BasicComponent;
use FormGenerator\Components\EndFieldset;
use FormGenerator\Components\Inputs\Hidden;
use FormGenerator\Components\Inputs\Option;
use FormGenerator\Components\Inputs\Select;
use FormGenerator\Components\Inputs\Radio;
use FormGenerator\Components\Inputs\Reset;
use FormGenerator\Components\Inputs\Submit;
use FormGenerator\Components\Inputs\Password;
use FormGenerator\Components\Inputs\TextArea;
use FormGenerator\Components\Inputs\Image;
use FormGenerator\Components\Label;

trait BasicAttributesTrait
{
    abstract protected function supportedAttributeTests(): array;

    #[\PHPUnit\Framework\Attributes\DataProvider('idProvider')]
    public function testIdAttribute(string $value, string $expected): void
    {
        $html = $this->input->id($value)->getHTML();
        if (in_array("id", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('id="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('id="' . $expected . '"', $html);
        }
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

    #[\PHPUnit\Framework\Attributes\DataProvider('valueProvider')]
    public function testValueAttribute(string $value, string $expected): void
    {
        $html = $this->input->value($value)->getHTML();
        if (!empty(trim($value))) {
            if (in_array("value", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('value="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('value="' . $expected . '"', $html);
            }
        } else {
            if (
                $this->input instanceof Option ||
                $this->input instanceof Radio ||
                $this->input instanceof Reset ||
                $this->input instanceof Submit ||
                $this->input instanceof Select
            ) {
                $this->assertStringContainsString('value=', $html);
            } else {
                $this->assertStringNotContainsString('value=', $html);
            }
        }
    }
    public static function valueProvider(): array
    {
        return [
            'simple value' => ['value', 'value'],
            'numeric value' => ['123', '123'],
            'null' => ['    ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('placeholderProvider')]
    public function testPlaceholderAttribute(string $value, string $expected): void
    {
        $html = $this->input->placeholder($value)->getHTML();
        if (!empty(trim($value))) {
            if (in_array("placeholder", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('placeholder="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('placeholder="' . $expected . '"', $html);
            }
        } else {
            $this->assertStringNotContainsString('placeholder=', $html);
        }
    }
    public static function placeholderProvider(): array
    {
        return [
            'basic' => ['placeholder', 'placeholder'],
            'with space' => ['Enter text', 'Enter text'],
            'null' => ['    ', '']
        ];
    }

    public function testAutofocusAttribute(): void
    {
        $html = $this->input->autofocus()->getHTML();
        if (in_array("autofocus", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('autofocus', $html);
        } else {
            $this->assertStringNotContainsString('autofocus', $html);
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('maxlengthProvider')]
    public function testMaxlengthAttribute(mixed $value, string $expected): void
    {
        $html = $this->input->maxlength($value)->getHTML();
        if (is_numeric($value)) {
            $value =  strval($value);
        }
        if (!empty(trim($value))) {
            if (in_array("maxlength", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('maxlength="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('maxlength="' . $expected . '"', $html);
            }
        } else {
            $this->assertStringNotContainsString('maxlength=', $html);
        }
    }
    public static function maxlengthProvider(): array
    {
        return [
            'string 1' => ["1", "1"],
            'int 1'    => [1, "1"],
            'string 1,1' => ["1,1", "11"],
            'null' => ['   ', '']
        ];
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('minlengthProvider')]
    public function testMinlengthAttribute(mixed $value, string $expected): void
    {
        $html = $this->input->minlength($value)->getHTML();
        if (is_numeric($value)) {
            $value =  strval($value);
        }
        if (!empty(trim($value))) {
            if (in_array("minlength", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('minlength="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('minlength="' . $expected . '"', $html);
            }
        } else {
            if ($this->input instanceof Password) {
                $this->assertStringContainsString('minlength=', $html);
            } else {
                $this->assertStringNotContainsString('minlength=', $html);
            }
        }
    }
    public static function minlengthProvider(): array
    {
        return [
            'string 1' => ["1", "1"],
            'int 1'    => [1, "1"],
            'string 1,1' => ["1,1", "11"],
            'null' => ['    ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('sizeProvider')]
    public function testSizeAttribute(int $value, string $expected): void
    {
        $html = $this->input->size($value)->getHTML();
        if (in_array("size", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('size="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('size="' . $expected . '"', $html);
        }
    }
    public static function sizeProvider(): array
    {
        return [
            'size 1' => [1, '1'],
            'size 10' => [10, '10'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('patternProvider')]
    public function testPatternAttribute(string $value, string $expected): void
    {
        $html = $this->input->pattern($value)->getHTML();
        if (!empty(trim($value))) {
            if (in_array("pattern", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('pattern="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('pattern="' . $expected . '"', $html);
            }
        } else {
            if ($this->input instanceof Password) {
                $this->assertStringContainsString('pattern=', $html);
            } else {
                $this->assertStringNotContainsString('pattern=', $html);
            }
        }
    }
    public static function patternProvider(): array
    {
        return [
            'letters only' => ['[A-Za-z]+', '[A-Za-z]+'],
            'digits only' => ['[0-9]+', '[0-9]+'],
            'null' => ['   ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('autocompleteProvider')]
    public function testAutocompleteAttribute(mixed $value, string $expected): void
    {
        $html = $this->input->autocomplete($value)->getHTML();

        if (is_bool($value)) {
            if ($value) {
                $value = "true";
            } else {
                $value = "false";
            }
        }

        if (in_array("autocomplete", $this->supportedAttributeTests(), true)) {
            if (!empty(trim($value))) {
                $this->assertStringContainsString('autocomplete="' . $expected . '"', $html);
            } else {
                $this->assertStringContainsString('autocomplete="on"', $html);
            }
        } else {
            $this->assertStringNotContainsString('autocomplete="' . $expected . '"', $html);
        }
    }

    public static function autocompleteProvider(): array
    {
        return [
            'string on' => ["on", "on"],
            'boolean true' => [true, "on"],
            'string off' => ["off", "off"],
            'boolean false' => [false, "off"],
            'null' => ['  ', ''],
            'bad value' => ['badValue', 'on']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('listProvider')]
    public function testListAttribute(string $value, string $expected): void
    {
        $html = $this->input->list($value)->getHTML();
        if (!empty(trim($value))) {
            if (in_array("list", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('list="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('list="' . $expected . '"', $html);
            }
        } else {
            $this->assertStringNotContainsString('list=', $html);
        }
    }
    public static function listProvider(): array
    {
        return [
            'simple list' => ['list', 'list'],
            'another list' => ['myList', 'myList'],
            'null' => ['  ', '']
        ];
    }
    public function testMultipleAttribute(): void
    {
        $html = $this->input->multiple()->getHTML();
        if (in_array("multiple", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('multiple', $html);
        } else {
            $this->assertStringNotContainsString('multiple', $html);
        }
    }
    public function testRequiredAttribute(): void
    {
        $html = $this->input->required()->getHTML();
        if (in_array("required", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('required', $html);
        } else {
            $this->assertStringNotContainsString('required', $html);
        }
    }
    public function testReadonlyAttribute(): void
    {
        $html = $this->input->readonly()->getHTML();
        if (in_array("readonly", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('readonly', $html);
        } else {
            $this->assertStringNotContainsString('readonly', $html);
        }
    }
    public function testDisabledAttribute(): void
    {
        $html = $this->input->disabled()->getHTML();
        if (in_array("disabled", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('disabled', $html);
        } else {
            $this->assertStringNotContainsString('disabled', $html);
        }
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('classProvider')]
    public function testClassAttribute(array|string $value, string $expected): void
    {
        $html = $this->input->class($value)->getHTML();
        if (is_array($value)) {
            $value = implode('', $value);
        }
        if (!empty(trim($value))) {
            if (in_array("class", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('class="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('class="' . $expected . '"', $html);
            }
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
            $html = $this->input->class($val)->getHTML();
            if (is_array($val)) {
                $value[$key] = implode(' ', $val);
            }
        }
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            if (in_array("class", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('class="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('class="' . $expected . '"', $html);
            }
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
        $html = $this->input->setAttributes($attributes)->getHTML();

        if (! $this->input instanceof EndFieldset) {
            foreach ($expectedContains as $str) {
                $this->assertStringContainsString($str, $html);
            }
        } else {
            foreach ($expectedContains as $str) {
                $this->assertStringNotContainsString($str, $html);
            }
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
        $html = $this->input->style($value)->getHTML();
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            if (in_array("style", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('style="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('style="' . $expected . '"', $html);
            }
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
            $html = $this->input->style($val)->getHTML();
            if (is_array($val)) {
                $value[$key] = implode(' ', $val);
            }
        }
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            if (in_array("style", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('style="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('style="' . $expected . '"', $html);
            }
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


    #[\PHPUnit\Framework\Attributes\DataProvider('minProvider')]
    public function testMinAttribute(mixed $value, string $expected): void
    {
        $html = $this->input->min($value)->getHTML();
        if (is_numeric($value)) {
            $value = strval($value);
        }
        if (!empty(trim($value))) {
            if (in_array("min", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('min="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('min="' . $expected . '"', $html);
            }
        } else {
            $this->assertStringNotContainsString('min=', $html);
        }
    }

    public static function minProvider(): array
    {
        return [
            'string 1' => ["1", "1"],
            'int 1'    => [1, "1"],
            'float 1.1' => [1.1, "1.1"],
            'string 1,1' => ["1,1", "1.1"],
            'null' => ['  ', '']
        ];
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('maxProvider')]
    public function testMaxAttribute(mixed $value, string $expected): void
    {
        $html = $this->input->max($value)->getHTML();
        if (is_numeric($value)) {
            $value = strval($value);
        }
        if (!empty(trim($value))) {
            if (in_array("max", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('max="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('max="' . $expected . '"', $html);
            }
        } else {
            $this->assertStringNotContainsString('max=', $html);
        }
    }

    public static function maxProvider(): array
    {
        return [
            'string 1' => ["1", "1"],
            'int 1'    => [1, "1"],
            'float 1.1' => [1.1, "1.1"],
            'string 1,1' => ["1,1", "1.1"],
            'null' => ['  ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formProvider')]
    public function testFormAttribute(string $value, string $expected): void
    {
        $html = $this->input->form($value)->getHTML();
        if (!empty(trim($value))) {
            if (in_array("form", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('form="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('form="' . $expected . '"', $html);
            }
        } else {
            $this->assertStringNotContainsString('form=', $html);
        }
    }
    public static function formProvider(): array
    {
        return [
            'basic form' => ['form', 'form'],
            'complex form' => ['myForm1', 'myForm1'],
            'null' => ['  ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('acceptProvider')]
    public function testAcceptAttribute(array|string $value, string $expected): void
    {
        $html = $this->input->accept($value)->getHTML();
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        if (!empty(trim($value))) {
            if (in_array("accept", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('accept="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('accept="' . $expected . '"', $html);
            }
        } else {
            $this->assertStringNotContainsString('accept=', $html);
        }
    }

    public static function acceptProvider(): array
    {
        return [
            'single accept' => ['accept', 'accept'],
            'multiple accept' => [['accept1', 'accept2'], 'accept1, accept2'],
            'null' => ['   ', '']
        ];
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('stepProvider')]
    public function testStepAttribute(int|string|float $value, string $expected): void
    {
        $html = $this->input->step($value)->getHTML();
        if (in_array("step", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('step="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('step="' . $expected . '"', $html);
        }
    }

    public static function stepProvider(): array
    {
        return [
            'step int' => [1, "1"],
            'step float' => [1.1, "1.1"],
            'step string' => ["1.1", "1.1"],
            'step string2' => ["1", "1"],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('spellcheckProvider')]
    public function testSpellcheckAttribute(mixed $value, string $expected): void
    {
        $html = $this->input->spellcheck($value)->getHTML();

        if (in_array("spellcheck", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('spellcheck="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('spellcheck="' . $expected . '"', $html);
        }
    }

    public static function spellcheckProvider(): array
    {
        return [
            'true' => [true, "true"],
            'boolean false' => [false, "false"]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('wrapProvider')]
    public function testWrapAttribute(string $value, string $expected): void
    {
        $html = $this->input->wrap($value)->getHTML();
        if (!empty(trim($value))) {
            if (in_array("wrap", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('wrap="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('wrap="' . $expected . '"', $html);
            }
        } else {
            if (
                $this->input instanceof TextArea ||
                get_class($this->input) == BasicComponent::class
            ) {
                $this->assertStringContainsString('wrap=', $html);
            } else {
                $this->assertStringNotContainsString('wrap=', $html);
            }
        }
    }
    public static function wrapProvider(): array
    {
        return [
            'soft' => ['soft', 'soft'],
            'hard' => ['hard', 'hard'],
            'null' => ['  ', '']
        ];
    }

    public function testHiddenAttribute(): void
    {
        $html = $this->input->hidden()->getHTML();
        if (in_array("hidden", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('hidden', $html);
        } else {
            if ($this->input instanceof Hidden) {
                $this->assertStringContainsString('type="hidden"', $html);
            } else {
                $this->assertStringNotContainsString('hidden', $html);
            }
        }
    }
    public function testRowsAttribute(): void
    {
        $html = $this->input->rows(1)->getHTML();

        if (in_array("rows", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('rows="1"', $html);
        } else {
            $this->assertStringNotContainsString('rows="1"', $html);
        }
    }
    public function testColsAttribute(): void
    {
        $html = $this->input->cols(1)->getHTML();

        if (in_array("cols", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('cols="1"', $html);
        } else {
            $this->assertStringNotContainsString('cols="1"', $html);
        }
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('dirProvider')]
    public function testDirAttribute(string $value, string $expected): void
    {
        $html = $this->input->dir($value)->getHTML();
        $value = strtolower(trim($value));
        if (!empty($value)) {
            if (in_array("dir", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('dir="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('dir="' . $expected . '"', $html);
            }
        } else {
            $this->assertStringNotContainsString('dir=', $html);
        }
    }


    public static function dirProvider(): array
    {
        return [
            'ltr upper' => ['LtR', 'ltr'],
            'rtl' => ['rtl', 'rtl'],
            'auto' => ['auto', 'auto'],
            'else' => ['else', 'else'],
            'null' => ['  ', '']
        ];
    }


    #[\PHPUnit\Framework\Attributes\DataProvider('titleProvider')]
    public function testTitleAttribute(string $value, string $expected): void
    {
        $html = $this->input->title($value)->getHTML();
        $value = trim($value);
        if (!empty($value)) {
            if (in_array("title", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('title="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('title="' . $expected . '"', $html);
            }
        } else {
            if ($this->input instanceof Password) {
                $this->assertStringContainsString('title=', $html);
            } else {
                $this->assertStringNotContainsString('title=', $html);
            }
        }
    }

    public static function titleProvider(): array
    {
        return [
            'basic' => ['title', 'title'],
            'complex' => ['My Title', 'My Title'],
            'null' => ['  ', '']
        ];
    }
    public function testCheckedAttribute(): void
    {
        $html = $this->input->checked()->getHTML();
        if (in_array("checked", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('checked', $html);
        } else {
            $this->assertStringNotContainsString('checked', $html);
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('srcProvider')]
    public function testSrcAttribute(string $value, string $expected): void
    {
        $html = $this->input->src($value)->getHTML();
        $value = trim($value);
        if (!empty($value)) {
            if (in_array("src", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('src="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('src="' . $expected . '"', $html);
            }
        } else {
            if ($this->input instanceof Image) {
                $this->assertStringContainsString('src=', $html);
            } else {
                $this->assertStringNotContainsString('src=', $html);
            }
        }
    }

    public static function srcProvider(): array
    {
        return [
            'basic' => ['src', 'src'],
            'path' => ['/img/test.png', '/img/test.png'],
            'null' => ['   ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('altProvider')]
    public function testAltAttribute(string $value, string $expected): void
    {
        $html = $this->input->alt($value)->getHTML();
        $value = trim($value);
        if (!empty($value)) {
            if (in_array("alt", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('alt="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('alt="' . $expected . '"', $html);
            }
        } else {
            if ($this->input instanceof Image) {
                $this->assertStringContainsString('alt=', $html);
            } else {
                $this->assertStringNotContainsString('alt=', $html);
            }
        }
    }

    public static function altProvider(): array
    {
        return [
            'basic' => ['alt', 'alt'],
            'complex' => ['My Alt', 'My Alt'],
            'null' => ['  ', '']
        ];
    }
    public function testWidthAttribute(): void
    {
        $html = $this->input->width(1)->getHTML();

        if (in_array("width", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('width="1"', $html);
        } else {
            $this->assertStringNotContainsString('width="1"', $html);
        }
    }
    public function testHeightAttribute(): void
    {
        $html = $this->input->height(1)->getHTML();

        if (in_array("height", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('height="1"', $html);
        } else {
            $this->assertStringNotContainsString('height="1"', $html);
        }
    }

    public function testSelectedAttribute(): void
    {
        $html = $this->input->selected()->getHTML();
        if (in_array("selected", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('selected', $html);
        } else {
            $this->assertStringNotContainsString('selected', $html);
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('typeProvider')]
    public function testTypeAttribute(string $value, string $expected): void
    {
        $html = $this->input->type($value)->getHTML();
        if (in_array("type", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('type="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('type="' . $expected . '"', $html);
        }
    }

    public static function typeProvider(): array
    {
        return [
            'basic' => ['myType', 'myType'],
            'custom' => ['custom', 'custom'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('labelProvider')]
    public function testLabelAttribute(string $value, string $expected): void
    {
        $html = $this->input instanceof Label
            ? $this->input->getHTML()
            : $this->input->label($value)->getHTML();

        if (empty(trim($value))) {
            $this->assertDoesNotMatchRegularExpression('//<label>/', $html);
            return;
        }

        if ($this->input instanceof Label) {
            $this->assertStringContainsString(
                '<label for="inputId" id="Label" name="Label">Label</label>',
                $html
            );
        } else {
            if (in_array("label", $this->supportedAttributeTests(), true)) {
                $this->assertMatchesRegularExpression(
                    '/<label for=("|\')[^\1]*\1>' . preg_quote($expected, '/') . '<\/label>/',
                    $html
                );
            } else {
                $this->assertDoesNotMatchRegularExpression(
                    '/<label for=("|\')[^\1]*\1>' . preg_quote($expected, '/') . '<\/label>/',
                    $html
                );
            }
        }
    }


    public static function labelProvider(): array
    {
        return [
            'basic' => ['Label', 'Label'],
            'custom' => ['My Label', 'My Label'],
            'null' => ['  ', '']
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('onclickProvider')]
    public function testOnclickAttribute(string $value, string $expected): void
    {
        $html = $this->input->onclick($value)->getHTML();
        $value = trim($value);
        if (!empty($value)) {
            if (in_array("onclick", $this->supportedAttributeTests(), true)) {
                $this->assertStringContainsString('onclick="' . $expected . '"', $html);
            } else {
                $this->assertStringNotContainsString('onclick="' . $expected . '"', $html);
            }
        } else {
            $this->assertStringNotContainsString('onclick=', $html);
        }
    }

    public static function onclickProvider(): array
    {
        return [
            'console log' => ['console.log("ahoj")', 'console.log("ahoj")'],
            'alert' => ['alert(1)', 'alert(1)'],
            'null' => ['  ', '']
        ];
    }

    public function testGetEchoesComponentHtml(): void
    {
        ob_start();
        $this->input->get();
        $output = ob_get_clean();

        $this->assertSame($this->input->getHtml(), $output);
    }
}
