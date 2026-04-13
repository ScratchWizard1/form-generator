<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Date;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Date::class)]
final class DateTest extends TestCase
{
    use BasicAttributesTrait;

    private Date $input;

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
        $this->input = new Date('Date');
    }
    public function testInputIsInstanceOfDate(): void
    {
        $this->assertInstanceOf(Date::class, $this->input);
    }
    public function testInputIsTypeOfDate(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="date"', $html);
    }
}
