<?php

declare(strict_types=1);

namespace FormGenerator\Tests\Validation;

use FormGenerator\Config\Globals;
use PHPUnit\Framework\TestCase;
use FormGenerator\Validation\ValidationResult;

#[\PHPUnit\Framework\Attributes\CoversClass(ValidationResult::class)]
final class ValidationResultTest extends TestCase
{
    private ValidationResult $result;

    protected function setUp(): void
    {
        parent::setUp();
        $this->result = new ValidationResult(['key' => 'value']);
    }
    public function testInputIsInstanceOfBasicComponent(): void
    {
        $this->assertInstanceOf(ValidationResult::class, $this->result);
    }
    public function testEmptyData()
    {
        $res = new ValidationResult([]);

        $result = $res->getData();
        $this->assertNull($result);

        $result = $res->getJson();
        $this->assertNull($result);

        $result = $res->getError();
        $this->assertNull($result);
    }
    public function testGetJson()
    {
        $result = $this->result->getJson();
        $this->assertJson($result);
    }
    public function testGetError()
    {
        $result = $this->result->getError();
        $this->assertNull($result);
    }
}
