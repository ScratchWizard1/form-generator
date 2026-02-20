<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Radio;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Radio::class)]
final class RadioTest extends TestCase
{
    use BasicAttributesTrait;

    private Radio $input;

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
            'checked',
            'autofocus',
            'title',
            'hidden',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Radio('Radio');
    }
    public function testInputIsInstanceOfRadio(): void
    {
        $this->assertInstanceOf(Radio::class, $this->input);
    }
    public function testInputIsTypeOfRadio(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="radio"', $html);
    }
}
