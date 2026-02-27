<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Text;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Text::class)]
final class TextTest extends TestCase
{
    use BasicAttributesTrait;

    private Text $input;

    protected function supportedAttributeTests(): array
    {
        return  [
            'id',
            'onclick',
            'value',
            'placeholder',
            'autofocus',
            'maxlength',
            'minlength',
            'size',
            'pattern',
            'autocomplete',
            'list',
            'required',
            'readonly',
            'disabled',
            'class',
            'style',
            'form',
            'spellcheck',
            'dir',
            'hidden',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Text('Text');
    }
    public function testInputIsInstanceOfText(): void
    {
        $this->assertInstanceOf(Text::class, $this->input);
    }
    public function testInputIsTypeOfText(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="text"', $html);
    }
}
