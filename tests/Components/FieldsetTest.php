<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Fieldset;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Fieldset::class)]
final class FieldsetTest extends TestCase
{
    use BasicAttributesTrait;

    private Fieldset $input;

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
        $this->input = new Fieldset();
    }
    public function testComponentsIsInstanceOfFieldet(): void
    {
        $this->assertInstanceOf(Fieldset::class, $this->input);
    }
    public function testComponentIsFieldset(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('<fieldset>', $html);
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

    public function testForAttribute(): void
    {
        $html = $this->input->for('fieldset-id')->getHTML();
        $this->assertStringContainsString('id="fieldset-id"', $html);
    }
}
