<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Hidden;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Hidden::class)]
final class HiddenTest extends TestCase
{
    use BasicAttributesTrait;

    private Hidden $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'value',
            'class',
            'style',
            'form',
            'title'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Hidden('Hidden');
    }
    public function testInputIsInstanceOfHidden(): void
    {
        $this->assertInstanceOf(Hidden::class, $this->input);
    }
    public function testInputIsTypeOfHidden(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="hidden"', $html);
    }
}
