<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Url;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Url::class)]
final class UrlTest extends TestCase
{
    use BasicAttributesTrait;

    private Url $input;

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
            'required',
            'readonly',
            'disabled',
            'class',
            'style',
            'form',
            'spellcheck',
            'hidden',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Url('Url');
    }
    public function testInputIsInstanceOfUrl(): void
    {
        $this->assertInstanceOf(Url::class, $this->input);
    }
    public function testInputIsTypeOfUrl(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="url"', $html);
    }
}
