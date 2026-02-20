<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Number;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Number::class)]
final class NumberTest extends TestCase
{
    use BasicAttributesTrait;

    private Number $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'value',
            'placeholder',
            'autofocus',
            'autocomplete',
            'list',
            'required',
            'readonly',
            'disabled',
            'class',
            'style',
            'form',
            'step',
            'min',
            'max',
            'hidden',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Number('Number');
    }
    public function testInputIsInstanceOfNumber(): void
    {
        $this->assertInstanceOf(Number::class, $this->input);
    }
    public function testInputIsTypeOfNumber(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="number"', $html);
    }
}
