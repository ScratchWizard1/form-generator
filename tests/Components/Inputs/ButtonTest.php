<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Button;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Button::class)]
final class ButtonTest extends TestCase
{
    use BasicAttributesTrait;

    private Button $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'value',
            'disabled',
            'class',
            'style',
            'form',
            'autofocus',
            'onclick',
            'hidden',
            'title',
            'label'
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Button('Button');
    }

    public function testInputIsInstanceOfButton(): void
    {
        $this->assertInstanceOf(Button::class, $this->input);
    }

    public function testSetTextButton(): void
    {
        $this->input->setText('Submit');
        $html = $this->input->getHTML();
        $this->assertStringContainsString('<button id="Button" name="Button">Submit</button>', $html);
    }

    public function testInputDefaultTextIsEmpty(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('<button id="Button" name="Button"></button>', $html);
    }

    public function testSetTextButtonInConstructor(): void
    {
        $buttonWithText = new Button('ButtonWithText', 'Click Me');
        $html = $buttonWithText->getHTML();
        $this->assertStringContainsString('<button id="ButtonWithText" name="ButtonWithText">Click Me</button>', $html);
    }

    public function testInputIsTypeOfButton(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('<button id="Button" name="Button"></button>', $html);
    }
}
