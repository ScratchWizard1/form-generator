<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Color;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Color::class)]
final class ColorTest extends TestCase
{
    use BasicAttributesTrait;

    private Color $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'value',
            'required',
            'readonly',
            'disabled',
            'class',
            'style',
            'form',
            'autofocus',
            'hidden',
            'title',
            'label'
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Color('Color');
    }
    public function testInputIsInstanceOfColor(): void
    {
        $this->assertInstanceOf(Color::class, $this->input);
    }
    public function testInputIsTypeOfColor(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="color"', $html);
    }
}
