<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Components\Inputs;

use PHPUnit\Framework\TestCase;
use FormGenerator\Components\Inputs\Option;
use FormGenerator\Components\Inputs\Select;
use FormGenerator\Tests\Traits\BasicAttributesTrait;
use FormGenerator\Config\Globals;

#[\PHPUnit\Framework\Attributes\CoversClass(Globals::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Select::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(Option::class)]
final class OptionTest extends TestCase
{
    use BasicAttributesTrait;

    private Option $input;
    private Select $select;

    protected function supportedAttributeTests(): array
    {
        return [
            'value',
            'disabled',
            'selected',
            'class',
            'style',
            'id',
            'onclick',
            'hidden',
            'title'
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->select = new Select("Select");
        $this->input = new Option("Option", $this->select);
    }

    public function testInputIsInstanceOfOption(): void
    {
        $this->assertInstanceOf(Option::class, $this->input);
    }

    public function testOptionDelegatesToParentSelect(): void
    {
        $label = 'Test label';

        $optionMock = $this->createMock(Option::class);

        $parentSelectMock = $this->createMock(Select::class);
        $parentSelectMock
            ->expects($this->once())
            ->method('option')
            ->with($label)
            ->willReturn($optionMock);

        $subject = new Option('Option', $parentSelectMock);

        $result = $subject->option($label);

        $this->assertSame($optionMock, $result);
    }
    public function testGetEchoesOptionHtml(): void
    {

        ob_start();
        $this->input->get();
        $output = ob_get_clean();

        $this->assertSame($this->input->getOption(), $output);
    }
}
