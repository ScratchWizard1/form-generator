<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Range;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Range::class)]
final class RangeTest extends TestCase
{
    use BasicAttributesTrait;

    private Range $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'value',
            'autofocus',
            'list',
            'required',
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
        $this->input = new Range('Range');
    }
    public function testInputIsInstanceOfRange(): void
    {
        $this->assertInstanceOf(Range::class, $this->input);
    }
    public function testInputIsTypeOfRange(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="range"', $html);
    }
}
