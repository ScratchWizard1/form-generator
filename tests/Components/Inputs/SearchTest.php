<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Search;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Search::class)]
final class SearchTest extends TestCase
{
    use BasicAttributesTrait;

    private Search $input;

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
            'autocomplete',
            'list',
            'hidden',
            'spellcheck',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Search('Search');
    }
    public function testInputIsInstanceOfSearch(): void
    {
        $this->assertInstanceOf(Search::class, $this->input);
    }
    public function testInputIsTypeOfSearch(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('type="search"', $html);
    }
}
