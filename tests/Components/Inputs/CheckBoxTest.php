<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\CheckBox;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(CheckBox::class)]
final class CheckBoxTest extends TestCase
{
    use BasicAttributesTrait;

    private CheckBox $input;

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
            'hidden',
            'title',
            'label'
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new CheckBox('CheckBox');
    }

    public function testInputIsInstanceOfCheckBox(): void
    {
        $this->assertInstanceOf(CheckBox::class, $this->input);
    }
    public function testInputIsTypeOfCheckBox(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="checkbox"', $html);
    }
}
