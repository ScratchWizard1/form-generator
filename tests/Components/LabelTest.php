<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Label;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Label::class)]
final class LabelTest extends TestCase
{
    use BasicAttributesTrait;

    private Label $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'for',
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
        $this->input = new Label('Label', 'inputId');
    }
    public function testComponentsIsInstanceOfLabel(): void
    {
        $this->assertInstanceOf(Label::class, $this->input);
    }
    public function testComponentIsLabel(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString(
            '<label for="inputId" id="Label" name="Label">Label</label>',
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

    public function testForAttribute(): void
    {
        $html = $this->input->for('fieldset-id')->getHTML();
        $this->assertStringContainsString('for="fieldset-id"', $html);
    }
}
