<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\BasicComponent;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(BasicComponent::class)]
final class BasicComponentTest extends TestCase
{
    use BasicAttributesTrait;

    private BasicComponent $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'name',
            'class',
            'style',
            'value',
            'hidden',
            'placeholder',
            'required',
            'disabled',
            'readonly',
            'maxlength',
            'minlength',
            'size',
            'pattern',
            'autocomplete',
            'multiple',
            'list',
            'autofocus',
            'form',
            'min',
            'max',
            'accept',
            'step',
            'spellcheck',
            'wrap',
            'rows',
            'cols',
            'dir',
            'title',
            'checked',
            'src',
            'alt',
            'width',
            'height',
            'selected',
            'type',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new BasicComponent();
    }
    public function testInputIsInstanceOfBasicComponent(): void
    {
        $this->assertInstanceOf(BasicComponent::class, $this->input);
    }
}
