<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Image;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Image::class)]
final class ImageTest extends TestCase
{
    use BasicAttributesTrait;

    private Image $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'value',
            'src',
            'alt',
            'disabled',
            'class',
            'style',
            'form',
            'width',
            'height',
            'autofocus',
            'hidden',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Image('Image', 'src', 'alt');
    }
    public function testInputIsInstanceOfImage(): void
    {
        $this->assertInstanceOf(Image::class, $this->input);
    }
    public function testInputIsTypeOfImage(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="image"', $html);
    }
}
