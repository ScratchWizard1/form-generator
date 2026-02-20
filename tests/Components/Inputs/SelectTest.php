<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Select;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;
use FormGenerator\Components\Inputs\Option;
use FormGenerator\ErrorHandling\FormGeneratorException;
use FormGenerator\ErrorHandling\FormErrorHandler;

#[\PHPUnit\Framework\Attributes\CoversClass(FormErrorHandler::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(FormGeneratorException::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Option::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Select::class)]
final class SelectTest extends TestCase
{
    use BasicAttributesTrait;

    private Select $input;

    protected function supportedAttributeTests(): array
    {
        return [
            'id',
            'onclick',
            'class',
            'style',
            'form',
            'size',
            'multiple',
            'required',
            'disabled',
            'autofocus',
            'hidden',
            'title',
            'label'
        ];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->input = new Select('Select');
    }
    public function testInputIsInstanceOfSelect(): void
    {
        $this->assertInstanceOf(Select::class, $this->input);
    }
    public function testInputIsTypeOfSelect(): void
    {
        $html = $this->input->getHTML();
        $this->assertMatchesRegularExpression(
            '~<select name="Select" id="Select">.*?</select>~s',
            $html
        );
    }
    public function testAddOptionToSelect(): void
    {
        $this->input->option('Option 1')->value('1');
        $this->input->option('Option 2')->value('2')->selected(true);
        $html = $this->input->getHTML();
        $this->assertStringContainsString('<option value="1">Option 1</option>', $html);
        $this->assertStringContainsString('<option value="2" selected>Option 2</option>', $html);
    }

    public function testAddMultipleOptionsToSelect(): void
    {
        $this->input->option(['Option 1', 'Option 2', 'Option 3'], ['1', '2', '3'], 'Option 3');
        $html = $this->input->getHTML();
        $this->assertStringContainsString('<option value="1">Option 1</option>', $html);
        $this->assertStringContainsString('<option value="2">Option 2</option>', $html);
        $this->assertStringContainsString('<option value="3" selected>Option 3</option>', $html);
    }
    public function testAddMultipleOptionsToSelectWithoutValue(): void
    {
        $this->input->option(['Option 1', 'Option 2', 'Option 3'], [], 'Option 3');
        $html = $this->input->getHTML();
        $this->assertStringContainsString('<option value="Option 1">Option 1</option>', $html);
        $this->assertStringContainsString('<option value="Option 2">Option 2</option>', $html);
        $this->assertStringContainsString('<option value="Option 3" selected>Option 3</option>', $html);
    }

    public function testGetFormGeneratorException(): void
    {
        $this->expectException(FormGeneratorException::class);
        $this->input->option(['Option 1', 'Option 2'], ['1'], 'Option 2');
    }
}
