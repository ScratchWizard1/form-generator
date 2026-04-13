<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Submit;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Submit::class)]
final class SubmitTest extends TestCase
{
    use BasicAttributesTrait;

    private Submit $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'value',
            'disabled',
            'class',
            'style',
            'form',
            'autofocus',
            'hidden',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Submit('Submit');
    }
    public function testInputIsInstanceOfSubmit(): void
    {
        $this->assertInstanceOf(Submit::class, $this->input);
    }
    public function testInputIsTypeOfSubmit(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="submit"', $html);
    }
}
