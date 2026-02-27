<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Email;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Email::class)]
final class EmailTest extends TestCase
{
    use BasicAttributesTrait;

    private Email $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'value',
            'placeholder',
            'autofocus',
            'maxlength',
            'minlength',
            'size',
            'autocomplete',
            'list',
            'multiple',
            'required',
            'readonly',
            'disabled',
            'class',
            'style',
            'form',
            'hidden',
            'spellcheck',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Email('Email');
    }
    public function testInputIsInstanceOfEmail(): void
    {
        $this->assertInstanceOf(Email::class, $this->input);
    }
    public function testInputIsTypeOfEmail(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="email"', $html);
    }
}
