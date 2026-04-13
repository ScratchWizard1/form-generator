<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Legend;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Legend::class)]
final class LegendTest extends TestCase
{
    use BasicAttributesTrait;

    private Legend $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'name',
            'class',
            'style',
            'title',
            'hidden'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Legend('Legend');
    }
    public function testComponentsIsInstanceOfLegend(): void
    {
        $this->assertInstanceOf(Legend::class, $this->input);
    }
    public function testComponentIsLegend(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString(
            '<legend>Legend</legend>',
            $html
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nameProvider')]
    public function testNameAttribute(string $value, string $expected): void
    {
        $html = $this->input->name($value)->getHTML();
        if (in_array("name", $this->supportedAttributeTests(), true)) {
            $this->assertStringContainsString('name="' . $expected . '"', $html);
        } else {
            $this->assertStringNotContainsString('name="' . $expected . '"', $html);
        }
    }
    public static function nameProvider(): array
    {
        return [
            'simple name' => ['name', 'name'],
            'complex name' => ['user[name]', 'user[name]'],
        ];
    }
}
