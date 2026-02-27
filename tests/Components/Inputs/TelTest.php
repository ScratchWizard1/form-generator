<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Tel;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Tel::class)]
final class TelTest extends TestCase
{
    use BasicAttributesTrait;

    private Tel $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'value',
            'placeholder',
            'autofocus',
            'maxlength',
            'minlength',
            'size',
            'autocomplete',
            'list',
            'required',
            'readonly',
            'disabled',
            'class',
            'style',
            'form',
            'spellcheck',
            'hidden',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Tel('Tel');
    }
    public function testInputIsInstanceOfTel(): void
    {
        $this->assertInstanceOf(Tel::class, $this->input);
    }
    public function testInputIsTypeOfTel(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="tel"', $html);
    }
}
