<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\TextArea;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(TextArea::class)]
final class TextAreaTest extends TestCase
{
    use BasicAttributesTrait;

    private TextArea $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'placeholder',
            'maxlength',
            'minlength',
            'required',
            'readonly',
            'disabled',
            'class',
            'style',
            'form',
            'autofocus',
            'spellcheck',
            'wrap',
            'rows',
            'cols',
            'dir',
            'hidden',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new TextArea('TextArea');
    }
    public function testInputIsInstanceOfTextArea(): void
    {
        $this->assertInstanceOf(TextArea::class, $this->input);
    }
    public function testInputIsTypeOfTextArea(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString(
            '<textarea name="TextArea" id="TextArea" wrap="soft"></textarea>',
            $html
        );
    }

    public function testSetValueTextArea(): void
    {
        $this->input->value('Sample text');
        $html = $this->input->getHTML();
        $this->assertStringContainsString(
            '<textarea name="TextArea" id="TextArea" wrap="soft">Sample text</textarea>',
            $html
        );
    }
    public function testSetValueandLabelTextArea(): void
    {
        $this->input->value('Sample text');
        $this->input->label('My TextArea');
        $html = $this->input->getHTML();
        $this->assertStringContainsString(
            '<label for="TextArea">My TextArea</label>'
                . '<textarea name="TextArea" id="TextArea" wrap="soft">Sample text</textarea>',
            $html
        );
    }
}
