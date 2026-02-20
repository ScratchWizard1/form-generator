<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\DateTime;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(DateTime::class)]
final class DateTimeTest extends TestCase
{
    use BasicAttributesTrait;

    private DateTime $input;

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
        $this->input = new DateTime('DateTime');
    }
    public function testInputIsInstanceOfDateTime(): void
    {
        $this->assertInstanceOf(DateTime::class, $this->input);
    }
    public function testInputIsTypeOfDatetime(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="datetime-local"', $html);
    }
}
