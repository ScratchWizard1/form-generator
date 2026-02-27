<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Reset;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Reset::class)]
final class ResetTest extends TestCase
{
    use BasicAttributesTrait;

    private Reset $input;

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
            'hidden',
            'autofocus',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Reset('Reset');
    }
    public function testInputIsInstanceOfReset(): void
    {
        $this->assertInstanceOf(Reset::class, $this->input);
    }
    public function testInputIsTypeOfReset(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="reset"', $html);
    }
}
