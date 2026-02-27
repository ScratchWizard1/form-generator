<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Month;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Month::class)]
final class MonthTest extends TestCase
{
    use BasicAttributesTrait;

    private Month $input;

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
        $this->input = new Month('Month');
    }
    public function testInputIsInstanceOfMonth(): void
    {
        $this->assertInstanceOf(Month::class, $this->input);
    }
    public function testInputIsTypeOfMonth(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="month"', $html);
    }
}
