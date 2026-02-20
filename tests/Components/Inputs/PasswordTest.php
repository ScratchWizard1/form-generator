<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Password;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Password::class)]
final class PasswordTest extends TestCase
{
    use BasicAttributesTrait;

    private Password $input;

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
            'required',
            'readonly',
            'disabled',
            'class',
            'style',
            'form',
            'dir',
            'title',
            'hidden',
            'pattern',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Password('Password');
    }
    public function testInputIsInstanceOfPassword(): void
    {
        $this->assertInstanceOf(Password::class, $this->input);
    }
    public function testInputIsTypeOfPassword(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="password"', $html);
    }
}
