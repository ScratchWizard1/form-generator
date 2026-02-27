<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\EndFieldset;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(EndFieldset::class)]
final class EndFieldsetTest extends TestCase
{
    use BasicAttributesTrait;

    private EndFieldset $input;

    protected function supportedAttributeTests(): array
    {
        return [];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new EndFieldset();
    }
    public function testComponentsIsInstanceOfEndFieldset(): void
    {
        $this->assertInstanceOf(EndFieldset::class, $this->input);
    }
    public function testComponentIsEndFieldset(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('</fieldset>', $html);
    }
}
