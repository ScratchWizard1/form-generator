<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Time;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Time::class)]
final class TimeTest extends TestCase
{
    use BasicAttributesTrait;

    private Time $input;

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
            'step',
            'autofocus',
            'hidden',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Time('Time');
    }
    public function testInputIsInstanceOfTime(): void
    {
        $this->assertInstanceOf(Time::class, $this->input);
    }
    public function testInputIsTypeOfTime(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="time"', $html);
    }
}
