<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Week;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Week::class)]
final class WeekTest extends TestCase
{
    use BasicAttributesTrait;

    private Week $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'value',
            'required',
            'disabled',
            'class',
            'style',
            'form',
            'min',
            'max',
            'autofocus',
            'hidden',
            'title',
            'step',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Week('Week');
    }
    public function testInputIsInstanceOfWeek(): void
    {
        $this->assertInstanceOf(Week::class, $this->input);
    }
    public function testInputIsTypeOfWeek(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="week"', $html);
    }
}
