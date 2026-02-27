<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components;

use PHPUnit\Framework\TestCase;
use FormGenerator\Form;
use FormGenerator\Components\HTML;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(HTML::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Form::class)]
final class HTMLTest extends TestCase
{
    use BasicAttributesTrait;

    private HTML $input;

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
        $parent = new Form("action", "id");
        $this->input = new HTML('<h1>Ahoj</h1>', "id", $parent);
    }
    public function testComponentsIsInstanceOfHTML(): void
    {
        $this->assertInstanceOf(HTML::class, $this->input);
    }
    public function testComponentIsHTML(): void
    {
        $html = $this->input->getHTML();
        $this->assertStringContainsString('<h1>Ahoj</h1>', $html);
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
