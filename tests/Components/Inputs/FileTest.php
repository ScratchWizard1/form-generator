<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\File;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(File::class)]
final class FileTest extends TestCase
{
    use BasicAttributesTrait;

    private File $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'required',
            'disabled',
            'class',
            'style',
            'form',
            'multiple',
            'accept',
            'autofocus',
            'hidden',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new File('File');
    }
    public function testInputIsInstanceOfFile(): void
    {
        $this->assertInstanceOf(File::class, $this->input);
    }
    public function testInputIsTypeOfFile(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="file"', $html);
    }
}
